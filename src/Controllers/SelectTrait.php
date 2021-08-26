<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;


trait SelectTrait
{


//    public function selectCrud($request)
//    {
//        //TODO : add comment and refactor this section
//        $key = $request['key'];
//        $loadMore = 1;
//        $offset = $request['page'] * 20;
//
//        if(in_array($key,$this->model->translatedAttributes)){
//            $items = $this->model::select('id')->whereTranslationLike($key, '%'.$request['search'].'%')->paginate(10)->toArray();
//            $data = str_replace('name','text',json_encode($items['data']));
//        }else{
//            $items = $this->model::select('id' ,"$key as text")->where($key,'LIKE', '%'.$request['search'].'%')->orderBy('text')->paginate(10)->toArray();
//            $data = json_encode($items['data']);
//        }
//
//        if(!$items['next_page_url']){
//            $loadMore = 0;
//        }
//        if($this->permission == 'owner'){
//            $items = $items->OnlyOwner();
//        }
//        $json = '{
//                    "results": '
//                    .$data.
//                    ', "pagination": {
//                        "more": '.$loadMore.
//                    '}
//                }';
//        return $json;
//    }

    public function select(request $request): JsonResponse
    {
        $items = [];
        if(isset($request['id']))
        {
            $items =  $this->find($request['id'], $request['field']);
            return Response::Json($items, 200);

        }

        if(isset($request['field']))
        {
            $items = $this->search($request['field'], $request['search']);
            return Response::Json($items, 200);

        }
        return Response::Json($items, 404);

    }

    private function search($field, $search)
    {
        $keyName = $this->model->getKeyName();
        return  $this->model::select($keyName.' as value' ,"$field as text")->where($field,'LIKE', '%'.$search.'%')->orderBy('text')->paginate();

    }

    private function find($id, $field){
        $keyName = $this->model->getKeyName();
        $id = json_decode($id);
        return $this->model->select($keyName.' as value' ,"$field as text")->whereIn($keyName,$id)->get();

    }



}
