<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\Condition;

/**
 *
 * @property string $label
 */
class DoesNotContainsCondition extends Condition
{
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return 'does not contains';
    }

    /**
     * @inheritDoc
     */
    public static function runValidation($inputValue, $ruleValue = null): bool
    {
        if (strpos($inputValue, $ruleValue) === false) {
            return true;
        }
        return false;
    }
}