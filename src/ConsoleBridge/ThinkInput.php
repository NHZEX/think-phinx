<?php
declare(strict_types=1);

namespace HZEX\Phinx\ConsoleBridge;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use think\console\Input;

class ThinkInput implements InputInterface
{

    /**
     * @var Input
     */
    private $input;

    public function __construct(Input $input)
    {
        $this->input = $input;
    }

    /**
     * @inheritDoc
     */
    public function getFirstArgument()
    {
        return $this->input->getFirstArgument();
    }

    /**
     * @inheritDoc
     */
    public function hasParameterOption($values, $onlyParams = false)
    {
        return $this->input->hasParameterOption($values);
    }

    /**
     * @inheritDoc
     */
    public function getParameterOption($values, $default = false, $onlyParams = false)
    {
        return $this->input->getParameterOption($values, $default);
    }

    /**
     * @inheritDoc
     */
    public function bind(InputDefinition $definition)
    {
        // ignore
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $this->input->validate();
    }

    /**
     * @inheritDoc
     */
    public function getArguments()
    {
        return $this->input->getArguments();
    }

    /**
     * @inheritDoc
     */
    public function getArgument($name)
    {
        return $this->input->getArgument($name);
    }

    /**
     * @inheritDoc
     */
    public function setArgument($name, $value)
    {
        $this->input->setArgument($name, $value);
    }

    /**
     * @inheritDoc
     */
    public function hasArgument($name)
    {
        return $this->input->hasArgument($name);
    }

    /**
     * @inheritDoc
     */
    public function getOptions()
    {
        return $this->input->getOptions();
    }

    /**
     * @inheritDoc
     */
    public function getOption($name)
    {
        return $this->input->getOption($name);
    }

    /**
     * @inheritDoc
     */
    public function setOption($name, $value)
    {
        $this->input->setOption($name, $value);
    }

    /**
     * @inheritDoc
     */
    public function hasOption($name)
    {
        return $this->input->hasOption($name);
    }

    /**
     * @inheritDoc
     */
    public function isInteractive()
    {
        return $this->input->isInteractive();
    }

    /**
     * @inheritDoc
     */
    public function setInteractive($interactive)
    {
        $this->input->setInteractive($interactive);
    }
}
