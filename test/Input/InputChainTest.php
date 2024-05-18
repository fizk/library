<?php

namespace Library\Input;

use PHPUnit\Framework\TestCase;
use Laminas\Filter\Word\CamelCaseToDash;
use Laminas\Filter\{
    UpperCaseWords,
    PregReplace,
    StringToLower
};
use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;

class InputChainTest extends TestCase
{
    public function testIsValid()
    {
        $chain = new InputChain();
        // GIVEN
        $input1 = (new Input('email'))
            ->attachFilter(new UpperCaseWords())
            ->attachValidator(new EmailAddress())
        ;

        $input2 = (new Input('name'))
            ->attachFilter(new UpperCaseWords())
        ;

        $chain->addInput($input1, 'email@server.com');
        $chain->addInput($input2, 'My Name');

        // WHEN
        $actual = $chain->isValid();

        // THEN
        $this->assertTrue($actual);
    }

    public function testIsNotValid()
    {
        $chain = new InputChain();
        // GIVEN
        $input1 = (new Input('email'))
            ->attachFilter(new UpperCaseWords())
            ->attachValidator(new EmailAddress())
        ;

        $input2 = (new Input('name'))
            ->attachFilter(new UpperCaseWords())
        ;

        $chain->addInput($input1, 'not an email');
        $chain->addInput($input2, 'My Name');

        // WHEN
        $actual = $chain->isValid();

        // THEN
        $this->assertFalse($actual);
    }

    public function testGetValuesAndMessagesWithNoError()
    {
        // GIVEN
        $input1 = (new Input('email'))
            ->attachValidator(new EmailAddress())
        ;

        $input2 = (new Input('name'))
            ->attachFilter(new CamelCaseToDash())
            ->attachFilter(new StringToLower())
        ;

        $chain = (new InputChain())
            ->addInput($input1, 'email@server.com')
            ->addInput($input2, 'ThisIsMyName')
        ;

        // WHEN
        $valid = $chain->isValid();

        // THEN
        $expected = [
            'email' => 'email@server.com',
            'name' => 'this-is-my-name',
        ];
        $actual = $chain->getValues();

        $this->assertTrue($valid);
        $this->assertEquals($expected, $actual);
        $this->assertCount(0, $chain->getMessages());
    }

    public function testGetValuesAndMessagesWithError()
    {
        // GIVEN
        $input1 = (new Input('email'))
            ->attachValidator(new EmailAddress())
        ;

        $input2 = (new Input('name'))
            ->attachFilter(new CamelCaseToDash())
            ->attachFilter(new StringToLower())
        ;

        $chain = (new InputChain())
            ->addInput($input1, 'this is not an email')
            ->addInput($input2, 'ThisIsMyName')
        ;

        // WHEN
        $valid = $chain->isValid();

        // THEN
        $expected = [
            'email' => 'this is not an email',
            'name' => 'this-is-my-name',
        ];
        $actual = $chain->getValues();

        $this->assertFalse($valid);
        $this->assertEquals($expected, $actual);
        $this->assertCount(1, $chain->getMessages());
        $this->assertArrayHasKey('email', $chain->getMessages());
    }
}
