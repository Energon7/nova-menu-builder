<?php

namespace Energon7\MenuBuilder;

use Laravel\Nova\ResourceTool;

class BuilderResourceTool extends ResourceTool
{

    /**
     * Get the displayable name of the resource tool.
     *
     * @return string
     */


    public function name()
    {
        return __('Menu Items');
    }



    public function jsonSerialize()
    {

        return array_merge([
            'component' => 'panel',
            'name' => $this->name,
            'showToolbar' => $this->showToolbar,
        ], $this->meta());
    }
    /**
     * Get the component name for the resource tool.
     *
     * @return string
     */
    public function component()
    {
        return 'menu-builder';
    }
}
