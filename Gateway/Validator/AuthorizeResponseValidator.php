<?php
/**
 * ChargeAfter
 *
 * @category    Payment Gateway
 * @package     Chargeafter_Payment
 * @copyright   Copyright (c) 2021 ChargeAfter.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      taras@lagan.com.ua
 */

namespace Chargeafter\Payment\Gateway\Validator;

class AuthorizeResponseValidator extends ResponseValidator
{
    /**
     * @return array
     */
    protected function getResponseValidators(): array
    {
        return array_merge(
            parent::getResponseValidators(),
            [
                function ($validationSubject) {
                    $response = $validationSubject['response'];

                    $rule = key_exists('state', $response) && mb_strtolower($response['state']) === 'authorized';
                    $message = __('ChargeAfter error. Unable to authorize the charge');
                    if (key_exists('message', $response)) {
                        $message .= ': ' . $response['message'];
                    }

                    return [$rule, $message];
                }
            ]
        );
    }
}
