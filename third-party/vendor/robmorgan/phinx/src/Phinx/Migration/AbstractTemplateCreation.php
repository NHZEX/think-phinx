<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */
namespace Phinx\Migration;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
abstract class AbstractTemplateCreation implements \Phinx\Migration\CreationInterface
{
    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;
    /**
     * @param \Symfony\Component\Console\Input\InputInterface|null $input Input
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output Output
     */
    public function __construct(?InputInterface $input = null, ?OutputInterface $output = null)
    {
        if ($input !== null) {
            $this->setInput($input);
        }
        if ($output !== null) {
            $this->setOutput($output);
        }
    }
    /**
     * @inheritDoc
     */
    public function getInput() : InputInterface
    {
        return $this->input;
    }
    /**
     * @inheritDoc
     */
    public function setInput(InputInterface $input) : \Phinx\Migration\CreationInterface
    {
        $this->input = $input;
        return $this;
    }
    /**
     * @inheritDoc
     */
    public function getOutput() : OutputInterface
    {
        return $this->output;
    }
    /**
     * @inheritDoc
     */
    public function setOutput(OutputInterface $output) : \Phinx\Migration\CreationInterface
    {
        $this->output = $output;
        return $this;
    }
}
