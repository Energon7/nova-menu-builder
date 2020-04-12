<?php

namespace Energon7\MenuBuilder\Http\Controllers;

use Energon7\MenuBuilder\Http\Models\Language;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Energon7\MenuBuilder\Http\Models\Menu;
use Energon7\MenuBuilder\Http\Models\MenuItems;
use Energon7\MenuBuilder\Http\Requests\NewMenuItemRequest;

class MenuController extends Controller
{
    /**
     * Return menu items for given menu
     *
     * @param   Request  $Request
     *
     * @return  Collection | json
     */
    public function items(Request $request)
    {
        if (!$request->has('menu')) {
            abort(503);
        }

        return Menu::with('parentItems')->find($request->get('menu'))->optionsMenu();
    }


    public function locales()
    {
       return Language::all()->map(function($lang) {
              return [
                  'code_field' => $lang->{config('menu-builder.code_field')},
                  'label' => $lang->{config('menu-builder.label_field')}
              ];
       });
    }

    /**
     * Save menu items when reordering
     *
     * @param   Request  $request
     *
     * @return  json
     */
    public function saveItems(Request $request)
    {
        $menu = Menu::find((int) $request->get('menu'));
        $items = $request->get('items');
        $i = 1;
        foreach ($items as $item) {
            $this->saveMenuItem($i, $item);
            $i++;
        }

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Create new menu item
     *
     * @param   NewMenuItemRequest  $request
     *
     * @return  json
     */
    public function createNew(NewMenuItemRequest $request)
    {
        $data = $request->all();
        $data['order'] = MenuItems::max('id') + 1;
        $menuItem = MenuItems::create($data);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Get menu item to edit
     *
     * @param   \Energon7\MenuBuilder\Http\Models\MenuItems  $item
     *
     * @return  json
     */
    public function edit(MenuItems $item)
    {
        return $item->toJson();
    }

    /**
     * Update the given menu item
     *
     * @param   \Energon7\MenuBuilder\Http\Models\MenuItems  $item
     * @param   NewMenuItemRequest  $request
     *
     * @return  json
     */
    public function update(MenuItems $item, NewMenuItemRequest $request)
    {
        $item->update($request->all());

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Destroy current menu item and all his childrens
     *
     * @param   \Energon7\MenuBuilder\Http\Models\MenuItems  $item
     *
     * @return  json
     */
    public function destroy(MenuItems $item)
    {
        if($item->can_delete) {
            $item->children()->delete();
            $item->delete();
        }
        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Save the menu item
     *
     * @param   int  $order
     * @param   array  $item
     * @param   int  $parentId
     *
     */
    private function saveMenuItem($order, $item, $parentId = null)
    {
        $menuItem = MenuItems::find($item['id']);
        $menuItem->order = $order;
        $menuItem->parent_id = $parentId;
        $menuItem->save();

        $this->checkChildren($item);
    }

    /**
     * Recurisve save menu items childrens
     *
     * @param   array  $item
     *
     */
    private function checkChildren($item)
    {
        if (count($item['children']) > 0) {
            $i = 1;
            foreach ($item['children'] as $child) {
                $this->saveMenuItem($i, $child, $item['id']);
                $i++;
            }
        }
    }
}
