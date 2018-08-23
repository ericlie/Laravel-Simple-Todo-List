<?php
namespace App\Helpers;

use Illuminate\Support\Str;

/**
 * Simple Enum Mutator Helper
 *
 * @author  Eric <eric.lie22@gmail.com>
 * @license MIT
 */
trait HasEnumMutator
{
    /**
     * Get the value of specific Enum
     *
     * @param string $key       enum name
     * @param string $attribute enum attribute
     *
     * @throws \Exception
     * @return int
     */
    public function pick(string $key, string $attribute)
    {
        return array_search($attribute, $this->get($key));
    }

    /**
     * Get Enum
     *
     * @param string $key [description]
     *
     * @throws \Exception
     * @return array       [description]
     */
    public function get(string $key): array
    {
        if ($this->hasGetMutator($key)) {
            return $this->mutateEnum($key);
        }
        throw new \Exception("Missing Enum");
    }

    /**
     * Determine if a get mutator exists for an Enum.
     *
     * @param string $key enum identifier
     *
     * @return bool
     */
    public function hasGetMutator(string $key): bool
    {
        return method_exists($this, 'get'.Str::studly($key).'Enum');
    }

    /**
     * Get the value of an Enum using its mutator.
     *
     * @param string $key enum identifier
     *
     * @return array
     */
    protected function mutateEnum(string $key): array
    {
        return $this->{'get'.Str::studly($key).'Enum'}();
    }

    /**
     * Common used
     *
     * @return array
     */
    private function getCommonEnum(): array
    {
        return [
            'none',
        ];
    }
}
