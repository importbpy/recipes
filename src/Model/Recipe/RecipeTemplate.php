<?php

namespace App\Model\Recipe;

class RecipeTemplate
{
    public static function getDescriptionTemplate(): string
    {
        return <<<EOF
## Ingredience:

- 100g ingredience
- 100g ingredience

## Postup:

1. První krok
2. Druhý krok
EOF;
    }
}
