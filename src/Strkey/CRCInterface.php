<?php

namespace Smartmoney\Stellar\Account\Strkey;

interface CRCInterface
{
    public function reset();
    public function update($data);
    public function finish();
}