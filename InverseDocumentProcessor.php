<?php

/**
 * Class InverseDocumentProcessor
 */
class InverseDocumentProcessor extends DocumentProcessor
{
    public function inverseReplaceUsingPair($target, $replacement, callable $callback = null)
    {
        return parent::replaceUsingPair($replacement, $target, $callback);
    }

    public function inverseReplaceUsingMapping(array $mapping, callable $callback = null)
    {
        $mapping = array_flip($mapping);
        return parent::replaceUsingMapping($mapping, $callback);
    }
}