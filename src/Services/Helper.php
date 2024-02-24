<?php

namespace MuckiSearchPlugin\Services;

class Helper
{
    public function createIndicesRequestBody(array $inputArray, array $mappings): array
    {
        $outputArray = [];
        $property = null;
        $propertyKey = null;

        foreach ($inputArray as $subArray) {
            $currentLayer = &$outputArray['properties'];

            $subArrayCounter = count($subArray);
            foreach ($subArray as $propertyKey => $property) {

                if (!isset($currentLayer[$property])) {
                    $currentLayer[$property] = [];
                }

                if($subArrayCounter === ($propertyKey+1)) {
                    $propertyType = &$currentLayer[$property]['type'];
                } else {
                    $currentLayer = &$currentLayer[$property]['properties'];
                }
            }

            //Set properties options
            if($property && $propertyKey >= 0) {
                $propertyType = $this->searchKeyFieldInMappings(
                    $mappings,
                    $property,
                    'dataType',
                    $propertyKey
                );
            }
        }

        return $outputArray;
    }

    public function createIndexingBody(array $inputArray): array
    {
        $outputArray = [];

        foreach ($inputArray as $item) {
            $propertyPath = $item['propertyPath'];
            $propertyValue = $item['propertyValue'];

            $currentLayer = &$outputArray;

            if(is_array($propertyPath)) {

                foreach ($propertyPath as $property) {

                    if (!isset($currentLayer[$property])) {
                        $currentLayer[$property] = [];
                    }

                    $currentLayer = &$currentLayer[$property];
                }
            }

            // Set the property value
            $currentLayer = $propertyValue;
        }

        return $outputArray;
    }

    public function searchKeyFieldInMappings(
        array $mappings,
        string $property,
        string $searchField,
        int $fieldCounter
    ): ?array
    {
        foreach ($mappings as $mapping) {

            $mappingElements = explode('.', $mapping['key']);
            if(array_key_exists($fieldCounter, $mappingElements) && $mappingElements[$fieldCounter] === $property) {
                return $mapping[$searchField];
            }
        }

        return null;
    }
    
    public function checkIndexSearchResults(?array $searchResult): bool
    {
        if(
            is_array($searchResult) &&
            array_key_exists('items', $searchResult) &&
            !empty($searchResult['items']) &&
            array_key_exists('id', $searchResult['items'][0])
        ) {
            return true;
        }

        return false;
    }

    public function getHashData(array|string $data): string
    {
        if(is_array($data)) {
            return md5(serialize($data));
        }
        return md5($data);
    }
}

