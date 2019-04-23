<?php

namespace Energon7\MenuBuilder\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;
use Energon7\MenuBuilder\Http\Models\MenuItems;

class Menu extends Model
{
    /**
     * @var string
     */
    protected $defaultParentTag = 'ul';
    protected $withChildren;
    protected $withActive;
    protected $with = ['parentItems'];
    /**
     * @var string
     */
    protected $defaultChildTag = 'li';

    /**
     * Boot
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->slug = str_slug($model->name);
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItems::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function parentItems(): HasMany
    {
        return $this->hasMany(MenuItems::class)->whereNull('parent_id')
            ->orderby('parent_id')
            ->orderby('order')
            ->orderby('name');
    }

    /**
     * Return menu items
     *
     * @return  Collection
     */
    public function optionsMenu()
    {
        return $this->parentItems()//->where('enabled', 1)
        ->orderby('parent_id')
            ->orderby('order')
            ->orderby('name')
            ->get();
    }

    /**
     * Return enabled menu items
     *
     * @return  Collection
     */
    public function optionsMenuEnabled()
    {
        return $this->parentItems;
    }

    /**
     * Render current menu items
     *
     * @param   string $parentTag
     * @param   string $childTag
     * @param   string $parentClass
     * @param   string $childClass
     *
     * @return  string
     */
    public function render($parentTag = null, $childTag = null, $parentClass = null, $childClass = null, $withChildren, $withActive)
    {
        $this->defaultParentTag = ($parentTag !== null) ? $parentTag : $this->defaultParentTag;
        $this->defaultChildTag = ($childTag !== null) ? $childTag : $this->defaultChildTag;
        $this->parentClass = $parentClass;
        $this->withChildren = $withChildren;
        $this->withActive = $withActive;
        $this->childClass = $childClass;

        $content = $this->renderItem($this->optionsMenuEnabled());

        return $this->parentHtmlBuilder($content);
    }

    /**
     * Render html for each item
     *
     * @param   collection $items
     *
     * @return  string
     */
    private function renderItem($items)
    {
        $menu = '';
        $current_route = Route::currentRouteName();
        foreach ($items as $item) {

            if (!$item->enabled) continue;

            $content = $item->html();

            $active = false;
            if($item->route)
            {
                if ($current_route == $item->route) $active = true;
                if($item->children->contains('route',$current_route)) $active = true;
            }


            $menu .= $this->buildTag($this->defaultChildTag, $item->classes, $active)
                . $content;

            if ($item->children->count() > 0 && $this->withChildren) {
                $childrenContent = $this->renderItem($item->children, $active);

                $menu .= $this->buildTag($this->defaultParentTag, $this->childClass)
                    . $childrenContent
                    . $this->closeTag($this->defaultParentTag);
            }

            $menu .= $this->closeTag($this->defaultChildTag);
        }

        return $menu;
    }

    /**
     * Generate htaml tags for parents
     *
     * @param   string $content
     *
     * @return  string
     */
    private function parentHtmlBuilder($content, $childClass = null)
    {
        return $this->buildTag($this->defaultParentTag, $childClass ?? $this->parentClass)
            . $content
            . $this->closeTag($this->defaultParentTag);
    }

    /**
     * Create html open tag for given tag
     *
     * @param   string $tag
     * @param   string | null $class
     *
     * @return  string
     */
    private function buildTag($tag, $class = null, $active = false)
    {
        $activeClass = $active ? 'active' : '';
        return "<{$tag} class='{$activeClass} {$class}'>";
    }

    /**
     * Close html tag
     *
     * @param   string $tag
     *
     * @return  string
     */
    private function closeTag($tag)
    {
        return "</{$tag}>";
    }
}
