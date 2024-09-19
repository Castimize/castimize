<?php

namespace Castimize\ApiTokens;

use Laravel\Nova\ResourceTool;

class ApiTokens extends ResourceTool
{
    /**
     * Get the displayable name of the resource tool.
     *
     * @return string
     */
    public function name()
    {
        return 'Api Tokens';
    }

    /**
     * Get the component name for the resource tool.
     *
     * @return string
     */
    public function component()
    {
        return 'api-tokens';
    }
}
