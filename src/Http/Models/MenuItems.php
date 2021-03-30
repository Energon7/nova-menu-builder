<?php

namespace Energon7\MenuBuilder\Http\Models;

use App\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;
use Energon7\MenuBuilder\Http\Models\Menu;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class MenuItems extends Model
{

    use HasTranslations;

    public $translatable = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->translatable = config('menu-builder.translatable_fields.menu_items');
    }
    /**
     * @var array
     */
    protected $fillable = ['menu_id','page_id' ,'name', 'url', 'route', 'parameters', 'target', 'parent_id', 'order', 'enabled', 'classes','seo_title','seo_description','seo_keywords'];

    /**
     * @var mixed
     */
    protected $with = ['children'];

    /**
     * @var array
     */
    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $appends = ['link', 'type', 'enabledClass'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order')->with('children');
    }
    public function page() {
        return $this->belongsTo(Page::class);
    }
    /**
     * @param $parentId
     *
     * @return Collection
     */
    public function itemsChildren($parentId)
    {
        return $this->whereParentId($parentId);
    }

    /**
     * Get Type attribute
     *
     * @return  string
     */
    public function getTypeAttribute()
    {
        if ($this->route != null) {
            return 'route';
        }

        return 'link';
    }

    /**
     * Get Enabled class attribute
     *
     * @return  string
     */
    public function getEnabledClassAttribute()
    {
        return ($this->enabled) ? 'enabled' : 'disabled';
    }

    /**
     * Get the link attribute
     *
     * @param   boolean  $absolute
     *
     * @return  string
     */
    public function getLinkAttribute($absolute = false)
    {
        if (is_null($this->parameters)) {
            $this->parameters = [];
        }

        if (is_string($this->parameters)) {
            $this->parameters = json_decode($this->parameters, true);
        } elseif (is_array($this->parameters)) {
            $this->parameters = $this->parameters;
        } elseif (is_object($this->parameters)) {
            $this->parameters = json_decode(json_encode($this->parameters), true);
        }

        if (!is_null($this->route)) {
            if (!Route::has($this->route)) {
                return '#';
            }

            return route($this->route, $this->parameters, $absolute);
        }

        if (Str::startsWith($this->url, 'http')) {
            $absolute = true;
        }

        if ($absolute) {
            return url($this->url);
        }

        return $this->url;
    }

    /**
     * Return the html link
     *
     * @return  string
     */
    public function html()
    {
        return '<a class="nav-link" href="'.$this->link.'" target="'.$this->target.'">'.$this->name.'</a>';
    }
}
