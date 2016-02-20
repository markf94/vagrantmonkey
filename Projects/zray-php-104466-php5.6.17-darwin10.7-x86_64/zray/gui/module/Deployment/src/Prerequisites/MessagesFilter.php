<?php
namespace Prerequisites;
use Prerequisites\Validator\Extension as ExtensionValidator;
use Prerequisites\Validator\Component as ComponentValidator;
use Prerequisites\Validator\Directive as DirectiveValidator;
use Prerequisites\Validator\Version as VersionValidator;

use ZendServer\Log\Log;

class MessagesFilter {
    public function filter($value) {
        if (! is_array($value)) {
            Log::warn(_t('Invalid value passed for filtering'));
            return array();
        }

        if (0 == count($value)) {
            return array();
        }

        $result = array();

        $validCodes = $this->getValidKeys();

        foreach ($value as $section => $items) {
            foreach ($items as $name => $message) {
                foreach ($message as $code => $string) {
                    if (! isset($validCodes[$code])) {
                        $result[$section][$name][$code] = $string;
                    }
                }
            }
        }

        return $result;

    }

    private function getValidKeys() {
        return array_flip(array(
            ExtensionValidator\Conflicts::VALID,
            ExtensionValidator\Equal::VALID,
            ExtensionValidator\Exclude::VALID,
            ExtensionValidator\Loaded::VALID,
            ExtensionValidator\Max::VALID,
            ExtensionValidator\Min::VALID,
            DirectiveValidator\Equal::VALID,
            DirectiveValidator\Exists::VALID,
            DirectiveValidator\Max::VALID,
            DirectiveValidator\Min::VALID,
            ComponentValidator\Conflicts::VALID,
            ComponentValidator\Equal::VALID,
            ComponentValidator\Exclude::VALID,
            ComponentValidator\Loaded::VALID,
            ComponentValidator\Max::VALID,
            ComponentValidator\Min::VALID,
            VersionValidator\Equal::VALID,
            VersionValidator\Exclude::VALID,
            VersionValidator\Max::VALID,
            VersionValidator\Min::VALID,
        ));
    }
}