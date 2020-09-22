<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $category = Category::with(['parent'])->orderBy('created_at', 'DESC')->paginate(15);
      
        $parent = Category::getParent()->orderBy('name', 'ASC')->get();
        $no = 1;
      
        return view('categories.index', compact('category', 'parent', 'no'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:50|unique:categories'
        ]);

        $request->request->add(['slug' => $request->name]);

        Category::create($request->except('_token'));

        return redirect(route('category.index'))->with(['success' => 'New Category was created.']);
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $parent = Category::getParent()->orderBy('name', 'ASC')->get();

        return view('categories.edit', compact('category', 'parent'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:50|unique:categories,name,' .$id
        ]);

        $category = Category::findOrFail($id);

        $category->update([
            'name' => $request->name,
            'pareng_id' => $request->parent_id
        ]);

        return redirect(route('category.index'))->with(['success' => 'Category was successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = Category::withCount(['child', 'product'])->findOrFail($id);
       if ($category->child_count == 0 && $category->product_count == 0) {
           $category->delete();

           return redirect(route('category.index'))->with(['success' => 'Category successfully deleted.']);
       }

       return redirect(route('category.index'))->with(['error' => "This category doesn't have child category!"]);
    }
}
