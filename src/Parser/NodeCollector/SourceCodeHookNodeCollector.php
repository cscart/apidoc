<?php
namespace CSCart\ApiDoc\Parser\NodeCollector;

use CSCart\ApiDoc\Parser\Context;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class SourceCodeHookNodeCollector implements NodeCollector
{
    /** @var Context */
    protected $parser_context;

    /** @var OutputInterface $output */
    protected $output;

    /**
     * @param OutputInterface $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * @param \CSCart\ApiDoc\Parser\Context $context
     */
    public function setParserContext(Context $context)
    {
        $this->parser_context = $context;
    }

    public function collectNodesToContext()
    {
        $exclude_mask_list = $this->parser_context->exclusion_masks;

        $exclude_filter = function (SplFileInfo $file) use ($exclude_mask_list) {
            foreach ($exclude_mask_list as $exclude_mask) {
                if (fnmatch($exclude_mask, $file->getRelativePathname())) {
                    return false;
                }
            }

            return true;
        };

        $progress_bar = new ProgressBar($this->output);

        $progress_bar->setFormat("%message% .. %elapsed%");
        $progress_bar->setMessage('Collecting files');

        $progress_bar->start();
        $found_files = (new Finder())
            ->files()
            ->in($this->parser_context->sources_directory)
            ->filter($exclude_filter)
            ->ignoreVCS(true)
            ->name('*.php');
        $progress_bar->finish();

        $progress_bar->setFormat(" %message%\n %current%/%max% [%bar%] %percent:3s%%\n Parsing file %filename%");
        $progress_bar->setMessage('Parsing files and collecting hooks');
        $progress_bar->setMessage('', 'filename');

        $progress_bar->start(sizeof($found_files));

        $total_hooks_counter = 0;
        foreach ($found_files as $file_info) {
            /** @var SplFileInfo $file_info */

            $progress_bar->setMessage($file_info->getRelativePathname(), 'filename');
            $progress_bar->display();

            $file_contents = file_get_contents($file_info->getRealPath());

            $hooks = $this->collectHooksFromSourceCode($file_contents);
            foreach ($hooks as $found_hook) {
                $found_hook->setFile($file_info->getRelativePathname());
                $this->parser_context->hooks[] = $found_hook;
            }

            $progress_bar->advance();
            $total_hooks_counter += sizeof($hooks);
        }
        $progress_bar->finish();

        $this->output->writeln(sprintf("Total <info>%u</info> hooks found\n", $total_hooks_counter));

        asort($this->parser_context->hooks);
    }

    protected function collectHooksFromSourceCode($source_code)
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $hook_collector = new HookCollectorNodeVisitor('fn_set_hook');
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor($hook_collector);
        $traverser->traverse($parser->parse($source_code));

        return $hook_collector->getFoundHooks();
    }
}