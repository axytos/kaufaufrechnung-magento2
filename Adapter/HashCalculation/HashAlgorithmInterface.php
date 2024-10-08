<?php

namespace Axytos\KaufAufRechnung\Adapter\HashCalculation;

interface HashAlgorithmInterface
{
    /**
     * @param string $input
     *
     * @return string
     */
    public function compute($input);
}
