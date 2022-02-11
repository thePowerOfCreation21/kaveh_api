<?php

namespace App\Services\CustomResponse;

use App\Exceptions\CustomException;
use function App\Helpers\convertToArray;

class Details
{
    protected $customResponseService = null;

    protected $properties = [
        'code' => 0,
        'http_code' => 500,
        'message' => 'nothing happened'
    ];

    public function __construct(CustomResponseService $customResponseService)
    {
        $this->customResponseService = $customResponseService;
    }

    public function __get($name)
    {
        return $this->getProperty($name);
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getProperty (string $key)
    {
        return $this->properties[$key] ?? null;
    }

    /**
     * @param string $key
     * @param $value
     * @return CustomResponseService|null
     */
    public function setProperty(string $key, $value): ?CustomResponseService
    {
        $this->properties[$key] = $value;

        return $this->customResponseService;
    }

    /**
     * @param $properties
     * @return CustomResponseService|null
     * @throws CustomException
     */
    public function setProperties ($properties): ?CustomResponseService
    {
        $properties = convertToArray($properties);

        foreach ($properties AS $key => $value)
        {
            $this->properties[$key] = $value;
        }

        return $this->customResponseService;
    }

    /**
     * @param $properties
     * @return void
     * @throws CustomException
     */
    public function putProperties ($properties)
    {
        $this->properties = [
            'code' => 0,
            'http_code' => 500,
            'message' => 'nothing happened'
        ];

        $this->setProperties($properties);
    }

    /**
     * @return CustomException
     * @throws CustomException
     */
    public function throwException (): CustomException
    {
        $properties = $this->properties;

        $customException = new CustomException($properties['message'], $properties['code'], $properties['http_code']);

        unset($properties['message']);
        unset($properties['code']);
        unset($properties['http_code']);

        if (!empty($properties))
        {
            $customException->setMoreDetails($properties);
        }

        throw $customException;
    }
}
