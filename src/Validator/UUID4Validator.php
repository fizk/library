<?php

declare(strict_types=1);

namespace Library\Validator;

use Laminas\Validator\ValidatorInterface;

class UUID4Validator implements ValidatorInterface
{
    private array $messages = [];

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible.
     */
    public function isValid($value): bool
    {
        if (preg_match('/[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}/', (string) $value, $m)) {
            return true;
        } else {
            $this->messages['uuidValidation'] = "Value Is not a valid UUID4";
            return false;
        }
    }

    /**
     * Returns an array of messages that explain why the most recent isValid()
     * call returned false. The array keys are validation failure message identifiers,
     * and the array values are the corresponding human-readable message strings.
     *
     * If isValid() was never called or if the most recent isValid() call
     * returned true, then this method returns an empty array.
     *
     * @return array<string, string>
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
