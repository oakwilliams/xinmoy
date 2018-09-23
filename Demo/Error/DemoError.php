<?php
/*
 * Demo Error
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   09/23/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Demo\Error;


use Xinmoy\Base\BaseError;

use Demo\Lang\ErrorLang;


/**
 * Demo Error
 */
class DemoError extends BaseError {
    const TEST = [ ErrorLang::DEMO_TEST, 2 ];
}
