<?php
namespace CSCart\ApiDoc\Console\Command;

use CSCart\ApiDoc\Parser\Context;
use CSCart\ApiDoc\Parser\Node\Generic;
use CSCart\ApiDoc\Parser\NodeCollector\PhpDocumentorOutputNodeCollector;
use CSCart\ApiDoc\Parser\NodeCollector\SourceCodeHookNodeCollector;
use CSCart\ApiDoc\Provider\MongoODMProvider;
use PhpParser\Node;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class CollectDocBlocksCommand extends Command
{
    /** @var Context $parser_context */
    protected $parser_context;

    /** @var Container $container */
    protected $container;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Collect all information of functions, classes, methods and hooks')
            ->addOption('source-path',
                's',
                InputOption::VALUE_REQUIRED,
                'Path to PHP files to be documented'
            )->addOption('ver',
                null,
                InputOption::VALUE_REQUIRED,
                'CS-Cart version'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container = new Container();

        $this->container->register(new MongoODMProvider());
        $this->container['fs'] = new Filesystem();

        $this->initContext($input);

        $this->collectHooksToContext($output);
        $this->collectPhpDocumentorOutputToContext();

        $this->parser_context->linkHooksToOwners();
        $this->parser_context->linkVersionToNodes();

        $this->persistContext();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     */
    protected function initContext(InputInterface $input)
    {
        $this->parser_context = new Context();
        $this->parser_context->version = $input->getOption('ver');
        $this->parser_context->sources_directory = rtrim(realpath($input->getOption('source-path')), '\\/') . '/';
        $this->parser_context->exclusion_masks = [
            '_docs/**',
            '_tools/**',
            'app/lib/**',
            'app/addons/store_import/**',
            'app/addons/**/lib/**',
            'app/addons/**/vendor/**',
            'app/payments/**',
            'var/**'
        ];
    }

    protected function collectHooksToContext(OutputInterface $output)
    {
        $hook_collector = new SourceCodeHookNodeCollector();
        $hook_collector->setOutput($output);
        $hook_collector->setParserContext($this->parser_context);
        $hook_collector->collectNodesToContext();
    }

    protected function collectPhpDocumentorOutputToContext()
    {
        $phpdocumentor_tmp_workdir = sys_get_temp_dir() . '/' . uniqid('phpdoc');
        $phpdocumentor_binary_path = realpath(ROOT_DIR . '/phpDocumentor.phar');

        $sources_directory = $this->parser_context->sources_directory;

        $exclusion_masks = array_map(function ($mask) use ($sources_directory) {
            return $sources_directory . $mask;
        }, $this->parser_context->exclusion_masks);

        $exclusion_masks = implode(',', $exclusion_masks);

        $phpdocumentor_exec_cmd = sprintf('%s -d %s -t %s -p -n --ignore=%s --template=xml',
            $phpdocumentor_binary_path,
            escapeshellarg($this->parser_context->sources_directory),
            escapeshellarg($phpdocumentor_tmp_workdir),
            escapeshellarg($exclusion_masks)
        );

        passthru($phpdocumentor_exec_cmd);

        $phpdocumentor_generated_xml_path = $phpdocumentor_tmp_workdir . '/structure.xml';

        $phpdocumentor_node_collector = new PhpDocumentorOutputNodeCollector($phpdocumentor_generated_xml_path);
        $phpdocumentor_node_collector->setParserContext($this->parser_context);
        $phpdocumentor_node_collector->collectNodesToContext();

        $this->container['fs']->remove($phpdocumentor_tmp_workdir);
    }

    protected function persistContext()
    {
        /** @var \Doctrine\ODM\MongoDB\DocumentManager $dm */
        $dm = $this->container['mongo.dm'];

        // Clear existing nodes of the contextual version
        $dm->createQueryBuilder(Generic::class)
            ->remove()
            ->field('version')
            ->equals($this->parser_context->version)
            ->getQuery()->execute();

        foreach ($this->parser_context->hooks as $hook) {
            $dm->persist($hook);
        }

        foreach ($this->parser_context->functions as $function_) {
            $dm->persist($function_);
        }

        foreach ($this->parser_context->classes as $class_) {
            $dm->persist($class_);
        }

        $dm->getSchemaManager()->ensureIndexes();

        $dm->flush();
    }
}