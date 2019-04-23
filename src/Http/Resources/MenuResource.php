<?php

namespace Energon7\MenuBuilder\Http\Resources;

use Illuminate\Http\Request;
use Energon7\MenuBuilder\BuilderResourceTool;
use Energon7\MenuBuilder\Http\Models\Menu;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Resource;

class MenuResource extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Menu::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * @var mixed
     */
    public static $displayInNavigation = false;

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Text::make(__('Name'), 'name')
                ->sortable()
                ->rules('required', 'max:255', 'unique:menus,name'),

            Text::make(__('Slug'), 'slug')->hideWhenCreating()->hideWhenUpdating(),

            Text::make(__('Menu Helper'), function () {
                return "<code class='p-2 bg-30 text-sm'><span class='text-primary'>{!!</span> <span class='text-info'>menu_builder(</span><span class='text-success'>'".$this->slug."'</span><span class='text-info'>)</span> <span class='text-primary'>!!}</span></code>";
            })->asHtml()->hideWhenCreating()->hideWhenUpdating(),

            BuilderResourceTool::make(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
        ];
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Menus';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Menu';
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'menu-builder';
    }
}
