@php
$langs = [
        ['id'=>'1','name'=>'fa'],
        ['id'=>'2','name'=>'en']
        ];

$langs=     json_decode(json_encode($langs));
@endphp
<div class="card card-default">
    <div class="card-header ">
        <h3>{{$field->display}}</h3>
    </div>
    <div class="card-body">
                <ul class="nav nav-tabs mb-4">
                    @foreach ($langs as $lang)
                    <li class="nav-item">
                        <a href="#language-{{$lang->id}}" class="nav-link @if($loop->first)  active @endif "
                            data-toggle="tab">{{$lang->name}}</a>
                    </li>
                    @endforeach
                </ul>

        <div class="tab-content">
            @foreach ($langs as $lang)
                {{-- This query find a relation where language id = $lang->id --}}
                @php $multiLanguageItem = $item->{$field->relation}->where('language_id',$lang->id)->first() @endphp
                
                {{-- if multiLanguage Item is exist system open edit form, else create form will be opened --}}
                {{-- edit --}}
                @if(isset($multiLanguageItem))
                    <div class="tab-pane fade @if($loop->first) active show @endif" id="language-{{$lang->id}}">
                        {!! Form::model($multiLanguageItem,
                                            [
                                            'route'=>["$field->routeName.update",$multiLanguageItem->getKey()],
                                            'method' => 'put',
                                            'class'=>'form-horizontal row',
                                            'enctype'=>'multipart/form-data'
                                            ]) !!}
                            @foreach ($field->fields as $subField)
                                @if(strpos($subField->visible, 'e') !== false)
                                    @if(!isset($subField->display))
                                        @php $subField->display = $subField->name; @endphp
                                    @endif
                                        @if(view()->exists("$crud->name::inputTypes.$subField->type"))
                                            @include("$crud->name::inputTypes.$subField->type",['field'=>$subField,'crud'=>$crud, 'item'=>$multiLanguageItem])
                                        @else
                                            @include("crud::scaffold.inputTypes.$subField->type",['field'=>$subField,'crud'=>$crud,'item'=>$multiLanguageItem])
                                        @endif
                                @endif
                            @endforeach
                        {!! Form::hidden('language_id', $lang->id) !!}

                        <div class="col-12">
                            <div class="form-group">
                                {!! Form::label('', '', ['class' => ' control-label']) !!}
                                <div class="">
                                    {!! Form::submit(trans('crud::panel.update'),['class'=>'btn btn-md btn-info save'])!!}
                                </div>
                            </div>
                        </div>

                        {!! Form::close() !!}
                    </div>
                @else
                {{-- create --}}
                    <div class="tab-pane fade @if($loop->first) active show @endif " id="language-{{$lang->id}}">
                        {!! Form::open(['route'=>["$field->routeName.store"],'method' => 'post', 'class'=>'form-horizontal row', 'enctype'=>'multipart/form-data']) !!}

                        {{-- this field is key column for create many to many relation --}}
                        {!! Form::hidden($field->key, $item->id) !!}
                        
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
                        <div class="col-12">
                            {{--Submit & Cancel--}}
                            <div class="form-group text-right">
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


@push('scripts')
    <script>
        $(`[data-toggle="tab"]`).click(function() {
            window.location.hash = $(this).attr('href');
        });

        function onHashChange() {
            var hash = window.location.hash;
            if (hash) {
                // using ES6 template string syntax
                $(`[data-toggle="tab"]`).removeClass('active');
                $(`[data-toggle="tab"][href="${hash}"]`).addClass('active');

                $('.tab-pane').removeClass('active').removeClass('show');
                    $(hash).addClass('active').addClass('show');
                }
            }
        onHashChange()


        window.onhashchange = function () {
            onHashChange()
        }
    </script>
@endpush
