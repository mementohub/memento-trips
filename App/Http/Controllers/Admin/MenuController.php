<?php

namespace App\Http\Controllers\Admin;

// ── Framework Dependencies ──────────────────────────────────────────────────
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

// ── Application Dependencies ────────────────────────────────────────────────
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;

/**
 * MenuController
 *
 * Manages the platform's navigation menu system from the admin panel.
 * Supports creating menus with assigned locations, adding/editing/deleting
 * menu items, and drag-and-drop reordering with nested parent-child
 * relationships via AJAX.
 *
 * @package App\Http\Controllers\Admin
 */
class MenuController extends Controller
{
    // ════════════════════════════════════════════════════════════════════════
    // ── Menu CRUD ───────────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Display a listing of all menus.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $menus = Menu::latest()->get();
        return view('admin.menus.index', compact('menus'));
    }

    /**
     * Show the form for creating a new menu.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $locations = get_registered_nav_menus();
        return view('admin.menus.create', compact('locations'));
    }

    /**
     * Store a newly created menu.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:menus',
            'location' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $menu = new Menu();
        $menu->name = $request->name;
        $menu->slug = Str::slug($request->name);
        $menu->location = $request->location;
        $menu->description = $request->description;
        $menu->status = $request->has('status') ? 1 : 0;
        $menu->save();

        return redirect()
            ->route('admin.menus.edit', $menu->id)
            ->with('success', 'Menu created successfully');
    }

    /**
     * Display the menu editor with its items and available pages.
     *
     * @param  int  $id  Menu ID
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $menu = Menu::findOrFail($id);
        $menuItems = MenuItem::where('menu_id', $id)
            ->where('parent_id', 0)
            ->orderBy('order')
            ->with(['children' => function ($query) {
            $query->orderBy('order');
        }])
            ->get();

        $locations = get_registered_nav_menus();
        $pages = \App\Models\Frontend::select('id', 'data_keys', 'data_values')->get();

        return view('admin.menus.edit', compact('menu', 'menuItems', 'locations', 'pages'));
    }

    /**
     * Update menu properties (name, location, description, status).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  Menu ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('menus')->ignore($menu->id)],
            'location' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $menu->name = $request->name;
        $menu->slug = Str::slug($request->name);
        $menu->location = $request->location;
        $menu->description = $request->description;
        $menu->status = $request->has('status') ? 1 : 0;
        $menu->save();

        return redirect()->back()->with('success', 'Menu updated successfully');
    }

    /**
     * Delete a menu and all its items.
     *
     * @param  int  $id  Menu ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        MenuItem::where('menu_id', $id)->delete();
        $menu->delete();

        return redirect()
            ->route('admin.menus.index')
            ->with('success', 'Menu deleted successfully');
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Menu Item CRUD ──────────────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Add a new menu item to a menu.
     *
     * Automatically assigns the next order position at the root level.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  Parent menu ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addMenuItem(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string',
            'type' => 'required|string',
        ]);

        $menu = Menu::findOrFail($id);

        $menuItem = new MenuItem();
        $menuItem->menu_id = $menu->id;
        $menuItem->title = $request->title;
        $menuItem->url = $request->url;
        $menuItem->icon_class = $request->icon_class;
        $menuItem->target = $request->target;
        $menuItem->type = $request->type;
        $menuItem->type_id = $request->type_id;
        $menuItem->parent_id = 0;

        // Append to end of root-level items
        $highestOrder = MenuItem::where('menu_id', $menu->id)
            ->where('parent_id', 0)
            ->max('order') ?? 0;

        $menuItem->order = $highestOrder + 1;
        $menuItem->status = 1;
        $menuItem->save();

        return redirect()->back()->with('success', 'Menu item added successfully');
    }

    /**
     * Update an existing menu item's properties.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  Menu item ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateMenuItem(Request $request, $id)
    {
        $menuItem = MenuItem::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string',
        ]);

        $menuItem->title = $request->title;
        $menuItem->url = $request->url;
        $menuItem->icon_class = $request->icon_class;
        $menuItem->target = $request->target;
        $menuItem->css_class = $request->css_class;
        $menuItem->status = $request->has('status') ? 1 : 0;
        $menuItem->save();

        return redirect()->back()->with('success', 'Menu item updated successfully');
    }

    /**
     * Delete a menu item and its children.
     *
     * @param  int  $id  Menu item ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteMenuItem($id)
    {
        $menuItem = MenuItem::findOrFail($id);
        MenuItem::where('parent_id', $id)->delete();
        $menuItem->delete();

        return redirect()->back()->with('success', 'Menu item deleted successfully');
    }

    /**
     * Get a menu item's data for AJAX editing.
     *
     * @param  int  $id  Menu item ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMenuItem($id)
    {
        $menuItem = MenuItem::findOrFail($id);
        return response()->json($menuItem);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ── Drag-and-Drop Ordering ──────────────────────────────────────────────
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Update the menu structure (order and parent-child relationships).
     *
     * Receives a JSON tree of menu item positions from the drag-and-drop
     * UI and recursively updates each item's order and parent_id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMenuStructure(Request $request)
    {
        $menuItems = json_decode($request->input('menu_items'), true);

        DB::beginTransaction();
        try {
            $this->updateMenuItemsOrder($menuItems);
            DB::commit();
            return response()->json(['success' => true]);
        }
        catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Recursively update menu items order and parent relationships.
     *
     * @param  array  $items     Array of item data with 'id' and optional 'children'
     * @param  int    $parentId  Parent menu item ID (0 for root)
     * @param  int    $order     Starting order position
     */
    private function updateMenuItemsOrder(array $items, int $parentId = 0, int $order = 0): void
    {
        foreach ($items as $item) {
            $menuItem = MenuItem::find($item['id']);
            if ($menuItem) {
                $menuItem->parent_id = $parentId;
                $menuItem->order = $order;
                $menuItem->save();

                if (!empty($item['children'])) {
                    $this->updateMenuItemsOrder($item['children'], $menuItem->id, 0);
                }
                $order++;
            }
        }
    }
}