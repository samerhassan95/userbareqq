<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;

class TranslationHelper
{
    /**
     * Get translated field value based on current locale
     * 
     * @param object $model The model instance
     * @param string $field The base field name (e.g., 'name', 'description')
     * @return mixed The translated value or fallback to English
     */
    public static function getTranslatedField($model, string $field)
    {
        $locale = App::getLocale();
        
        if ($locale === 'ar') {
            $arField = $field . '_ar';
            // Return Arabic if exists, otherwise fallback to English
            return $model->{$arField} ?? $model->{$field};
        }
        
        // Default to English
        return $model->{$field};
    }

    /**
     * Get all translatable fields for a model
     * 
     * @param object $model The model instance
     * @param array $fields Array of field names to translate
     * @return array Associative array with translated values
     */
    public static function getTranslatedFields($model, array $fields): array
    {
        $result = [];
        
        foreach ($fields as $field) {
            $result[$field] = self::getTranslatedField($model, $field);
        }
        
        return $result;
    }
}
