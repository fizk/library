<?php

namespace Library\Form;

use Laminas\Filter\ToNull;
use Library\Input\Input;
use PHPUnit\Framework\TestCase;
use Laminas\Filter\UpperCaseWords;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;

class FormTest extends TestCase
{
    public function testValid()
    {
        // GIVEN
        $data = [
            'name' => 'my name',
            'email' => 'email@mail.com',
        ];
        $form = new class ($data) extends Form
        {
            public function getValidationConfig(): array
            {
                return [
                    'name' => (new Input('name'))
                        ->attachFilter(new UpperCaseWords())
                        ->attachValidator(new NotEmpty()),
                    'email' => (new Input('email'))
                        ->attachValidator(new NotEmpty()),
                ];
            }
            public function getModel(): object
            {
                return new \stdClass();
            }
        };

        // WHEN
        $valid = $form->isValid();

        // THEN
        $this->assertTrue($valid);
    }

    public function testInvalid()
    {
        // GIVEN
        $data = [
            'name' => 'my name',
            'email' => 'not an email',
        ];
        $form = new class ($data) extends Form
        {
            public function getValidationConfig(): array
            {
                return [
                    'name' => (new Input('name'))
                        ->attachFilter(new UpperCaseWords())
                        ->attachValidator(new NotEmpty()),
                    'email' => (new Input('email'))
                        ->attachValidator(new EmailAddress())
                        ->attachValidator(new NotEmpty()),
                ];
            }
            public function getModel(): object
            {
                return new \stdClass();
            }
        };

        // WHEN
        $valid = $form->isValid();

        // THEN
        $this->assertFalse($valid);
    }

    public function testMissingValue()
    {
        // GIVEN
        $data = [
            'email' => 'valid@email.com',
        ];
        $form = new class ($data) extends Form
        {
            public function getValidationConfig(): array
            {
                return [
                    'name' => (new Input('name'))
                        ->attachFilter(new UpperCaseWords())
                        ->attachFilter(new ToNull())
                        ->attachValidator(new NotEmpty()),
                    'email' => (new Input('email'))
                        ->attachValidator(new EmailAddress())
                        ->attachValidator(new NotEmpty()),
                ];
            }
            public function getModel(): object
            {
                return new \stdClass();
            }
        };

        // WHEN
        $valid = $form->isValid();

        // THEN
        $this->assertFalse($valid);
    }

    public function testEmptyValue()
    {
        // GIVEN
        $data = [
            'name' => '',
            'email' => 'valid@email.com',
        ];
        $form = new class ($data) extends Form
        {
            public function getValidationConfig(): array
            {
                return [
                    'name' => (new Input('name'))
                        ->attachFilter(new UpperCaseWords())
                        ->attachFilter(new ToNull())
                        ->attachValidator(new NotEmpty()),
                    'email' => (new Input('email'))
                        ->attachValidator(new EmailAddress())
                        ->attachValidator(new NotEmpty()),
                ];
            }
            public function getModel(): object
            {
                return new \stdClass();
            }
        };

        // WHEN
        $valid = $form->isValid();

        // THEN
        $this->assertFalse($valid);
    }

    public function testReturnedModel()
    {
        // GIVEN
        $data = [
            'name' => 'Name Nameson',
            'email' => 'valid@email.com',
        ];
        $form = new class ($data) extends Form
        {
            public function getValidationConfig(): array
            {
                return [
                    'one' => (new Input('name'))
                        ->attachFilter(new UpperCaseWords())
                        ->attachFilter(new ToNull())
                        ->attachValidator(new NotEmpty()),
                    'two' => (new Input('email'))
                        ->attachValidator(new EmailAddress())
                        ->attachValidator(new NotEmpty()),
                ];
            }
            public function getModel(): object
            {
                return (object) $this->getInputChain()->getValues();
            }
        };

        // WHEN
        $valid = $form->isValid();

        $expected = (object) [
            'name' => 'Name Nameson',
            'email' => 'valid@email.com'
        ];
        $actual = $form->getModel();

        // THEN
        $this->assertTrue($valid);
        $this->assertEquals($expected, $actual);
    }

    public function testConfigWithNoKeys()
    {
        // GIVEN
        $data = [
            'name' => 'Name Nameson',
            'email' => 'valid@email.com',
        ];
        $form = new class ($data) extends Form
        {
            public function getValidationConfig(): array
            {
                return [
                    (new Input('name'))
                        ->attachFilter(new UpperCaseWords())
                        ->attachFilter(new ToNull())
                        ->attachValidator(new NotEmpty()),

                    (new Input('email'))
                        ->attachValidator(new EmailAddress())
                        ->attachValidator(new NotEmpty()),
                ];
            }
            public function getModel(): object
            {
                return (object) $this->getInputChain()->getValues();
            }
        };

        // WHEN
        $valid = $form->isValid();

        $expected = (object) [
            'name' => 'Name Nameson',
            'email' => 'valid@email.com'
        ];
        $actual = $form->getModel();

        // THEN
        $this->assertTrue($valid);
        $this->assertEquals($expected, $actual);
    }
}
