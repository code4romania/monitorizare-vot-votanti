<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Transformers\FormTransformer;
use App\Http\Controllers\Controller;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Http\Response;
use Dingo\Api\Routing\Helpers;
use App\Form;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FormController extends Controller
{
    use Helpers;
    /**
     * @var FormTransformer
     */
    private $formTransformer;

    /**
     * @param FormTransformer $formTransformer
     */
    public function __construct(FormTransformer $formTransformer)
    {
        $this->formTransformer = $formTransformer;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 20);
        $forms = Form::with('user')->paginate($limit);
        return response()->json([
            'data' => $this->formTransformer->transformCollection($forms->all()),
            'paginator' => $this->getPaginator($forms)
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public
    function store(Request $request)
    {
        $rules = [
            'title' => 'required|max:200',
            'status' => 'in:Active,Inactive',
            'fields' => 'required|max:500',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            throw new StoreResourceFailedException('Could not create new form!', $validator->errors());
        }
        $request->merge([
            'status' => $request->get('status', 'Inactive'),
        ]);
        try {
            $form = new Form($request->all());
            $form->save();
            return response()->json([
                'data' => $this->formTransformer->transform($form)
            ], Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            return $this->response->error('could_not_create_form', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public
    function show($id)
    {
        $form = Form::find($id);
        if (!$form) {
            return $this->notFoundResponse();
        }
        return response()->json(['data' => $form]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public
    function update(Request $request, $id)
    {
        $rules = [
            'title' => 'max:200',
            'status' => 'in:Active,Inactive',
            'fields' => 'max:500',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            throw new StoreResourceFailedException('Could not edit form.', $validator->errors());
        }
        try {
            $form = Form::findOrFail($id);
            $form->update($request->all());
            return response()->json([
                'data' => $this->formTransformer->transform($form)
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $modelNotFoundException) {
            return $this->notFoundResponse();
        } catch (\Exception $exception) {
            return $this->response->error('could_not_edit_form', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public
    function destroy(int $id)
    {
        try {
            Form::findOrFail($id)->delete();
            return $this->response->noContent();
        } catch (ModelNotFoundException $modelNotFoundException) {
            return $this->notFoundResponse();
        } catch (\Exception $exception) {
            return $this->response->error('could_not_delete_form', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public
    function activate(int $id)
    {
        try {
            $form = Form::findOrFail($id);
            $form->setAttribute('status', Form::ACTIVE);
            $form->save();
            return $this->response->noContent();
        } catch (ModelNotFoundException $modelNotFoundException) {
            return $this->notFoundResponse();
        } catch (\Exception $exception) {
            return $this->response->error('could_not_activate_form', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public
    function deactivate(int $id)
    {
        try {
            $form = Form::findOrFail($id);
            $form->setAttribute('status', Form::INACTIVE);
            $form->save();
            return $this->response->noContent();
        } catch (ModelNotFoundException $modelNotFoundException) {
            return $this->notFoundResponse();
        } catch (\Exception $exception) {
            return $this->response->error('could_not_deactivate_form', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
