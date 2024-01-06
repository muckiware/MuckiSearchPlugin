<?php declare(strict_types=1);

namespace MuckiSearchPlugin\Core\Content\ServerOptions\Elasticsearch;

use MuckiSearchPlugin\Core\Content\ServerOptions\MappingOptionsInterface;

class MappingOptions implements MappingOptionsInterface
{
    public function getDataTypes(): array
    {
        return array(
            array(
                'key' => 'binary',
                'label' => 'Binary',
                'desc' => 'Binary value encoded as a Base64 string.'
            ),
            array(
                'key' => 'boolean',
                'label' => 'Boolean',
                'desc' => 'true and false values.'
            ),
            array(
                'key' => 'keyword',
                'label' => 'Keyword',
                'desc' => 'The keyword family, including keyword, constant_keyword, and wildcard.'
            ),
            array(
                'key' => 'numbers',
                'label' => 'Numbers',
                'desc' => 'Numeric types, such as long and double, used to express amounts'
            ),
            array(
                'key' => 'dates',
                'label' => 'Dates',
                'desc' => 'Date types, including date and date_nanos.'
            ),
            array(
                'key' => 'alias',
                'label' => 'Alias',
                'desc' => 'Defines an alias for an existing field.'
            ),
            array(
                'key' => 'object',
                'label' => 'Object',
                'desc' => 'A JSON object.'
            ),
            array(
                'key' => 'flattened',
                'label' => 'Flattened',
                'desc' => 'An entire JSON object as a single field value.'
            ),
            array(
                'key' => 'nested',
                'label' => 'Nested',
                'desc' => 'A JSON object that preserves the relationship between its subfields.'
            ),
            array(
                'key' => 'join',
                'label' => 'Join',
                'desc' => 'Defines a parent/child relationship for documents in the same index.'
            ),
            array(
                'key' => 'range',
                'label' => 'Range',
                'desc' => 'Range types, such as long_range, double_range, date_range, and ip_range.'
            ),
            array(
                'key' => 'ip',
                'label' => 'IP',
                'desc' => 'IPv4 and IPv6 addresses.'
            ),
            array(
                'key' => 'version',
                'label' => 'Version',
                'desc' => 'Software versions. Supports Semantic Versioning precedence rules.'
            ),
            array(
                'key' => 'murmur3',
                'label' => 'Murmur3',
                'desc' => 'Compute and stores hashes of values.'
            ),
            array(
                'key' => 'aggregate_metric_double',
                'label' => 'aggregate_metric_double',
                'desc' => 'Pre-aggregated metric values.'
            ),
            array(
                'key' => 'histogram',
                'label' => 'Histogram',
                'desc' => 'Pre-aggregated numerical values in the form of a histogram.'
            ),
            array(
                'key' => 'text',
                'label' => 'Text',
                'desc' => 'the traditional field type for full-text content such as the body of an email or the description of a product.',
                'parameter' => array(
                    'analyzer' => array(
                        array(
                            'key' => 'standard',
                            'label' => 'Standard',
                            'desc' => 'The standard analyzer divides text into terms on word boundaries, as defined by the Unicode Text Segmentation algorithm. It removes most punctuation, lowercases terms, and supports removing stop words.'
                        ),
                        array(
                            'key' => 'simple',
                            'label' => 'Simple',
                            'desc' => 'The simple analyzer breaks text into tokens at any non-letter character, such as numbers, spaces, hyphens and apostrophes, discards non-letter characters, and changes uppercase to lowercase.'
                        ),
                        array(
                            'key' => 'simple',
                            'label' => 'Simple',
                            'desc' => 'The simple analyzer breaks text into tokens at any non-letter character, such as numbers, spaces, hyphens and apostrophes, discards non-letter characters, and changes uppercase to lowercase.'
                        ),
                        array(
                            'key' => 'whitespace',
                            'label' => 'Whitespace',
                            'desc' => 'The whitespace analyzer breaks text into terms whenever it encounters a whitespace character.'
                        ),
                        array(
                            'key' => 'stop',
                            'label' => 'Stop',
                            'desc' => 'The stop analyzer is the same as the simple analyzer but adds support for removing stop words. It defaults to using the _english_ stop words.'
                        ),
                        array(
                            'key' => 'keyword',
                            'label' => 'keyword',
                            'desc' => 'The keyword analyzer is a “noop” analyzer which returns the entire input string as a single token.'
                        ),
                        array(
                            'key' => 'pattern',
                            'label' => 'Pattern',
                            'desc' => 'The pattern analyzer uses a regular expression to split the text into terms. The regular expression should match the token separators not the tokens themselves. The regular expression defaults to \W+ (or all non-word characters).'
                        ),
                        array(
                            'key' => 'keyword',
                            'label' => 'keyword',
                            'desc' => 'The keyword analyzer is a “noop” analyzer which returns the entire input string as a single token.'
                        ),
                    )
                )
            ),
            array(
                'key' => 'match_only_text',
                'label' => 'match_only_text',
                'desc' => 'the traditional field type for full-text content such as the body of an email or the description of a product.'
            )
        );
    }
}

