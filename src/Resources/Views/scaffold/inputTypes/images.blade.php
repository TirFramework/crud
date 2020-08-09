@php

    $name = $field->name;
    
    // remove '[' and ']' from field name because fix upload problem
    $index = preg_replace('/[\[\]]/i', '_' , $field->name);

    $fieldName = $field->name.'[]';
    $images[0] = '';
    if( isset( $item->{$name} )){
        if ($item->{$name} != null) {
            if(  count($item->{$name})  ){
                $images = $item->{$name} ;
            }
        }
    }
    
@endphp

{!! Form::label($field->name, trans($crud->name."::panel.$field->display"), ['class' => 'col-md-12 control-label']) !!}


<div class="{{$field->col ?? 'col-12 col-md-12'}}" style=" margin-bottom: 40px; padding: 0 0 0 5px; ">


<style>

    .remove-item{
        top: 50%;
        transform: translateY(-50%);
    }
</style>

    <div id="cloningimages" class="sortable">

    @foreach ($images as $image)
    @isset($image)

        <div class="item" style=" margin-bottom: 15px;">


            <div class="form-group" style="    margin: 0 0 15px 15px; ">
                    <div class="input-group" style="    width: 100%;">
                        <span class="input-group-btn" style="display: flex;
                        width: 100%;
                        align-items: center;">

                            <div class="image-holder" id="{{$index}}_holder-{{$loop->index}}" id-template="{{$index}}_holder-xxx" style="margin: 0 15px 0 0;
                                max-height: 100px;
                            }">
                                @isset($image)
                                    <img src="{{ $image ?? null }}" alt="" style="max-width: 100px; max-height: 100px">
                                @endisset
                            </div>


                            <a id-template="{{$index}}-xxx"
                               id="{{$index}}-{{$loop->index}}"

                                data-input="{{$index}}_input-{{$loop->index}}"
                                data-input-template="{{$index}}_input-xxx"
                                data-preview="{{$index}}_holder-{{$loop->index}}"
                                data-preview-template="{{$index}}_holder-xxx"
                                 class="image-btn btn btn-primary">
                                <i class="fas fa-image"></i> {{trans('crud::panel.choose')}}
                            </a>
                                
                            {!! Form::text($fieldName, $image  ,['class' => 'form-control','id'=>$index.'_input-'.$loop->index, 'id-template'=>$index.'_input-xxx',   'name-template'=>$field->name.'[xxx]', 'placeholder'=> trans("$crud->name::panel.$field->name")])!!}
                        </span>

                    </div>
                    {{--<img id="{{$index}}_holder" @isset($image) src="{{url('/').'/'.$image}}" @endisset class="image-holder">--}}

            </div>
        </div>
        @endisset

    @endforeach


    </div>

</div>



@push('firstScripts')
    <script>
        let fieldimage = new additionalField('#cloningimages');

        console.log(fieldimage.dataId)
        fieldimage.callback = function () {


            $('#{{$index}}-'+ fieldimage.dataId).filemanager('image');   //btn image

            // taggable();

            $('#{{$index}}_holder-'+ fieldimage.dataId).find('[src]').attr('src', '');


        };
    </script>
@endpush


@push('scripts')
    <script>
            @foreach ($images as $image)

                $('#{{$index}}-{{$loop->index}}').filemanager('image');   //btn image

            @endforeach

    </script>
@endpush