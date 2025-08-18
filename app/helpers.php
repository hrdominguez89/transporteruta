<?php

if (! function_exists('ars')) {
    /**
     * Formatea números como ARS: $ 1.234,56
     */
    function ars($value): string
    {
        return '$ ' . number_format((float) $value, 2, ',', '.');
    }
}
