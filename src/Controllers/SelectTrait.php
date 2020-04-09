<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\Request;


trait SelectTrait
{

        /**
     * This function select model and get data for relation select boxes in views
     *  if permission == owner return only owner item
     * @return eloquent
     */
    public function selectCrud($request)
    {
        //TODO : add comment and refactor this section
        $key = $request['key'];
        $loadMore = 1;
        $offset = $request['page'] * 20;

        if(in_array($key,$this->model->translatedAttributes)){
            $items = $this->model::select('id')->whereTranslationLike($key, '%'.$request['search'].'%')->paginate(10)->toArray();
            $data = str_replace('name','text',json_encode($items['data']));
        }else{
            $items = $this->model::select('id' ,"$key as text")->where($key,'LIKE', '%'.$request['search'].'%')->orderBy('text')->paginate(10)->toArray();
            $data = json_encode($items['data']);
        }

        if(!$items['next_page_url']){
            $loadMore = 0;
        }
        if($this->permission == 'owner'){
            $items = $items->OnlyOwner();
        }
        $json = '{
                    "results": ' 
                    .$data.
                    ', "pagination": {
                        "more": '.$loadMore.
                    '}
                }';
        return $json;
    }

    public function select(request $request)
    {
        return $this->selectCrud($request);
    }

}
