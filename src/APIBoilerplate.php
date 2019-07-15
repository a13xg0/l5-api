<?php

namespace Specialtactics\L5Api;

use Config;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Illuminate\Support\Str;

class APIBoilerplate
{
    /**
     * Case type constants for configuring responses
     */
    const CAMEL_CASE = 'camel-case';
    const SNAKE_CASE = 'snake-case';
    const DEFAULT_CASE = self::CAMEL_CASE;

    /**
     * Case type config path
     */
    const CASE_TYPE_CONFIG_PATH = 'api.formatsOptions.caseType';

    /**
     * The header which can be used to override config provided case type
     */
    const CASE_TYPE_HEADER = 'X-Accept-Case-Type';

    /**
     * @var null|string Cache this value for a given request
     */
    protected static $requestedKeyCaseFormat = null;

    /**
     * Get the required 'case type' for transforming response data
     *
     * @return string
     */
    public static function getResponseCaseType()
    {
        $format = static::$requestedKeyCaseFormat;

        if (! is_null($format)) {
            return $format;
        }

        // See if the client is requesting a specific case type
        $caseFormat = request()->header(static::CASE_TYPE_HEADER, null);
        if (! is_null($caseFormat)) {
            if ($caseFormat == static::CAMEL_CASE) {
                $format = static::CAMEL_CASE;
            } elseif ($caseFormat == static::SNAKE_CASE) {
                $format = static::SNAKE_CASE;
            }
        }

        // Get case format from config (default case)
        if (is_null($format)) {
            $caseFormat = Config(static::CASE_TYPE_CONFIG_PATH);

            // Figure out required case
            if ($caseFormat == static::CAMEL_CASE || empty($caseFormat)) {
                $format = static::CAMEL_CASE;
            } elseif ($caseFormat == static::SNAKE_CASE) {
                $format = static::SNAKE_CASE;
            } else {
                throw new BadRequestHttpException('Invalid case type specified in API config.');
            }
        }

        // Save and return
        static::$requestedKeyCaseFormat = $format;

        return $format;
    }

    /**
     * Format the provided string into the required case response format, for attributes (ie. keys)
     *
     * @param string $attributeString
     * @return string
     */
    public static function formatCaseAccordingToResponseFormat($attributeString)
    {
        $format = static::getResponseCaseType();

        if ($format == static::CAMEL_CASE) {
            $attributeString = Str::camel($attributeString);
        } else {
            $attributeString = Str::snake($attributeString);
        }

        return $attributeString;
    }

    /**
     * Format the provided key string into the required case response format
     *
     * @deprecated Use the updated function name
     * @param string $key
     * @return string
     */
    public static function formatKeyCaseAccordingToReponseFormat($value)
    {
        return self::formatCaseAccordingToResponseFormat($value);
    }
}
