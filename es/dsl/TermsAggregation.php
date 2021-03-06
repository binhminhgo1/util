<?php
namespace go1\util\es\dsl;

use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation as ONGRTermsAggregation;

class TermsAggregation extends ONGRTermsAggregation
{
    private $size;

    public function __construct($name, $field = null, $script = null, $size = 10)
    {
        parent::__construct($name, $field, $script);
        $this->size = $size;
    }

    public function getArray()
    {
        return array_filter(
            [
                'field'  => $this->getField(),
                'script' => $this->getScript(),
                'size'   => $this->size,
            ]
        );
    }
}
