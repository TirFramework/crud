@php 
$langs = [
        ['id'=>'1','name'=>'fa'],
        ['id'=>'2','name'=>'en']
        ];

$langs=     json_decode(json_encode($langs));
@endphp

<div class="col-lg-12">
    <h3>{{$field->display}}</h3>
</div>
<div class="col-xs-12">
    <div class="panel panel-default">
        <div class="panel-body tabs">
            <ul class="nav nav-tabs">
                @foreach ($langs as $lang)
                    <li @if($loop->first) class="active" @endif>
                        <a href="#language-{{$lang->id}}" data-toggle="tab" aria-expanded="true">{{$lang->name}}</a></li>
                @endforeach
            </ul>

            <div class="tab-content">
                @foreach ($langs as $lang)
                    {{-- This query find a relation where language id = $lang->id --}}
                    @php $multiLanguageItem = $item->{$field->relation}->where('language_id',$lang->id)->first() @endphp

                    {{-- if multiLanguage Item is exist system open create form, else create form will be opened --}}
                    @if(isset($multiLanguageItem))
                        <div class="tab-pane fade in @if($loop->first) active @endif" id="language-{{$lang->id}}">
                            {!! Form::model($multiLanguageItem,
                                             [
                                                'route'=>["$field->routeName.update",$multiLanguageItem->getKey()],
                                                'method' => 'put', 
                                                'class'=>'form-horizontal row-border',
                                                'enctype'=>'multipart/form-data'
                                                ]) !!}
                                @foreach ($field->fields as $subField)
                                    @if(strpos($subField->visible, 'e') !== false)
                                        @if(!isset($subField->display))
                                            @php $subField->display = $subField->name; @endphp
                                        @endif
                                            @if(view()->exists("$crud->name::inputTypes.$subField->type"))
                                                @include("$crud->name::inputTypes.$subField->type",['field'=>$subField,'crud'=>$crud])
                                            @else
                                                @include("crud::scaffold.inputTypes.$subField->type",['field'=>$subField,'crud'=>$crud])
                                            @endif
                                    @endif
                                @endforeach
                            {!! Form::hidden('language_id', $lang->id) !!}
                            <div class="form-group">
                                {!! Form::label('', '', ['class' => 'col-md-2 control-label']) !!}
                                <div class="col-md-10">
                                    {!! Form::submit(trans('crud::panel.update'),['class'=>'btn btn-md btn-info save'])!!}
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    @else
                        <div class="tab-pane fade @if($loop->first) active @endif in" id="{{$lang->name}}">
                            {!! Form::open(['route'=>["$field->routeName.store"],'method' => 'post', 'class'=>'form-horizontal row-border', 'enctype'=>'multipart/form-data']) !!}
                            @foreach ($field->fields as $subField)
                                @if(strpos($subField->visible, 'c') !== false)
                                    @if(!isset($subField->display))
                                        @php $subField->display = $subField->name; @endphp
                                    @endif
                                    @if(view()->exists("$crud->name::inputTypes.$subField->type"))
                                        @include("$crud->name::inputTypes.$subField->type",['field'=>$subField,'crud'=>$crud])
                                    @else
                                        @include("crud::scaffold.inputTypes.$subField->type",['field'=>$subField,'crud'=>$crud])
                                    @endif
                                @endif
                            @endforeach

                            {!! Form::hidden('language_id', $lang->id) !!}

                            {{--Submit & Cancel--}}
                            <div class="form-group">
                                {!! Form::label('', '', ['class' => 'col-md-2 control-label']) !!}
                                <div class="col-md-10">
                                    {!! Form::submit(trans('crud::panel.update'),['class'=>'btn btn-md btn-info save'])!!}
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    @endif
                @endforeach
            </div>

        </div>
    </div>
</div>

