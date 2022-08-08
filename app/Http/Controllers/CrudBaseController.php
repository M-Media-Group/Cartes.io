<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class CrudBaseController extends Controller
{

    /**
     * The model namespaced path.
     *
     * @var string
     */
    protected string $model;

    /**
     * The model class name.
     *
     * @var string
     */
    protected string $classBaseName;

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // parent::__construct();
        $this->classBaseName = str_replace('Controller', '', class_basename($this));
        $this->model = 'App\\Models\\' . $this->classBaseName;
        App::bind(Model::class, $this->model);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', $this->model);

        $request->validate([
            'per_page' => 'numeric|integer|lte:500',
        ]);

        return $this->model::filterAndExpand($request)->simplePaginate($request->input('per_page', 15));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', $this->model);
        $data = $this->model::create($request->all());
        $eventName = 'App\\Events\\' . $this->classBaseName . 'Created';
        $eventName::dispatch($data);
        return $data;
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Skill $skill
     * @param int $id the ID of the resource
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Model $model)
    {
        $this->authorize('view', $model);
        return $model;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id the ID of the resource
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Model $model)
    {
        $this->authorize('update', $model);
        return $model->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id the ID of the resource
     * @return \Illuminate\Http\Response
     */
    public function destroy(Model $model)
    {
        $this->authorize('delete', $model);
        return $model->delete();
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param int $id the ID of the resource
     * @return \Illuminate\Http\Response
     */
    public function restore(Model $model)
    {
        $this->authorize('restore', $model);
        return $model->restore();
    }
}
