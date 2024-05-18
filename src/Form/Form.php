<?php

namespace Library\Form;

use Library\Input\InputChain;
use Library\Input\InputChainInterface;

abstract class Form
{
    private ?bool $valid = null;

    /**
     *
     * @return \Library\Input\InputInterface[]
     */
    abstract public function getValidationConfig(): array;

    abstract public function getModel(): object;

    public function __construct(private array $data = [], private InputChainInterface $inputChain = new InputChain())
    {
    }

    protected function getInputChain(): InputChainInterface
    {
        if ($this->valid === null) {
            throw new FormException('::isValid has not been called');
        }

        return $this->inputChain;
    }

    public function isValid()
    {
        if ($this->valid !== null) {
            return $this->valid;
        }

        foreach ($this->getValidationConfig() as $key => $value) {
            if (array_key_exists($value->getName(), $this->data)) {
                $this->inputChain->addInput($value, $this->data[$value->getName()]);
            } else {
                $this->inputChain->addInput($value, null);
            }
        }

        return $this->valid = $this->inputChain->isValid();
    }

    public function getMessages(): array
    {
        if ($this->valid === null) {
            throw new FormException('::isValid has not been called');
        }

        return $this->inputChain->getMessages();
    }
}
