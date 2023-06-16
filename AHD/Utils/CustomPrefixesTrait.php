<?php

namespace AHDCS\AHD\Utils;

/**
 * Trait to add a custom `$prefixes` property to sniffs and utility functions
 * to validate the property value.
 *
 * @package AHDCreative\AHDCS
 * @author  Artek
 *
 * @since   1.0.0
 */
trait CustomPrefixesTrait
{
    /**
     * The prefix which are allowed to be used.
     *
     * The prefix(es) should be in the exact case as expected.
     *
     * {@internal For most sniff implementing this property, this _should_
     * be one string prefix per plugin.
     * However, during the transition period - when, for instance, all old
     * hook names are being deprecated and the new hooks put in place -,
     * two prefixes (old and new) will be allowed.
     * At a future point in time, this property should be changed
     * to allow only a single string.}}
     *
     * @var string[]|string
     */

    public $prefixes = [];

    /**
     * Target prefixes after validation
     *
     * @var string[]
     */
    protected $validated_prefixes = [];

    /**
     * Cache the previous set of prefixes
     * It prevent to have the same prefix validated over and over again.
     *
     * @var string[]
     */
    protected $previous_prefixes = [];

    /**
     * Prepare the prefixes
     * Checks and makes sure that:
     * - "Namespace"-like prefixes do not start with a `\` and end with a `\`.
     * - Non-namespace-like prefixes do not start with a `_` and end with a `_`.
     *
     * @return void
     */
    protected function validate_prefixes()
    {
        if ($this->previous_prefixes == $this->prefixes) {
            return;
        }

        // Set the cache before validation
        $this->previous_prefixes = $this->prefixes;
        $prefixes = (array)$this->prefixes;
        $prefixes = \array_filter(\array_map('trim', $prefixes));
        if (empty($prefixes)) {
            $this->validated_prefixes = [];
            return;
        }

        // Allow extra rules.
        $prefixes = $this->filter_prefixes($prefixes);
        $validated = [];
        foreach ($prefixes as $prefix) {
            if (\strpos($prefix, '\\') !== false) {
                $prefix = \trim($prefix, '\\');
                $validated[] = $prefix . '\\';
            } else {
                // Old Style
                $prefix = \trim($prefix, '_');
                $validated[] = $prefix . '_';
            }
        }

        // Use reverse natural sorting to get the longest prefix first.
        \rsort($validated, (\SORT_NATURAL | \SORT_FLAG_CASE));

        // Set validate prefixes cache
        $this->validated_prefixes = $validated;
    }

    /**
     * Overloadable method to do custom prefix filtering prior to validation.
     *
     * @param string[] $prefixes The unvalidated prefixes.
     *
     * @return string[]
     */

    protected function filter_prefixes($prefixes) {
        return $prefixes;
    }

    /**
     * Filter out all prefixes which don't contain a namespace separator.
     *
     * @param string[] $prefixes The unvalidated prefixes.
     *
     * @return string[]
     */
    protected function filter_allow_only_namespace_prefixes($prefixes) {
        $filtered = [];
        foreach ($prefixes as $prefix) {
            if ( \strpos( $prefix, '\\' ) === false) {
                continue;
            }
            $filtered = $prefix;
        }
        return $filtered;
    }

    /**
     * Filter out all prefixes which only contain lowercase characters.
     *
     * @param string[] $prefixes The unvalidated prefixes.
     *
     * @return string[]
     */
    protected function filter_exclude_lowercase_prefixes($prefixes) {
        $filtered = [];
        foreach ($prefixes as $prefix) {
            if (\strtolower( $prefix ) === $prefix) {
                continue;
            }
            $filtered = $prefix;
        }
        return $filtered;
    }
}
