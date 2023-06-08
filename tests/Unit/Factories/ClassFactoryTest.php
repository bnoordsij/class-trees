<?php

namespace Tests\Unit\Factories;

use Bnoordsij\ClassTrees\Factory\ClassFactory;
use Tests\TestCase;

class ClassFactoryTest extends TestCase
{
    public function dataNamespaceRules(): array
    {
        return [
            [['App\A\C'], 'App\A\B', false], // not exact match
            [['\App'], 'App\A\B', false],
            [['App'], 'App\A\B', false],
            [['App'], '\App\A\B', false],
            [[1, '', 0, null], '\App\A\B', false],
            [['App\%'], '\App\A\B', false], // unsupported char
            [['*\A\B'], '\App\A\B', false], // we're not going to support these
            [['\app\a\b'], '\App\A\B', false], // casesensitive
            [[null], '\App\A\B', false],

            [[], '\App\A\B', true],
            [['App\A\B'], 'App\A\B', true], // exact match
            [[1, 'App\A\B', '', null, 'nope'], 'App\A\B', true], // at least 1 matches
            [['\App\A\B'], 'App\A\B', true],
            [['\\App\A\B'], '\App\A\B', true],
            [['\\App\\A\\B'], '\App\A\B', true],
            [['App\*'], 'App\A\B', true],
            [['App\*'], '\App\A\B', true],
            [['\App\*'], 'App\A\B', true],
            [['\App\*'], '\App\A\B', true],
            [['\App\\*'], '\App\A\B', true],
            [['\App\\*'], '\\App\\A\\B', true],
            [['\App\***'], '\App\A\B', true], // I don't mind this one
        ];
    }

    /** @dataProvider dataNamespaceRules */
    public function test_filter_namespace_rules(array $rules, string $fqn, bool $output): void
    {
        $arguments = [
            'fqn' => $fqn,
            'namespaceRules' => collect($rules),
        ];
        $return = $this->callMethod(ClassFactory::class, 'filterNamespaceRules', $arguments);

        self::assertSame($return, $output);
    }
}
