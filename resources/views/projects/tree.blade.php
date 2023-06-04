@extends('base')

@section('body')
    <div class="js-draw-tree">
    <span class="hidden">
        <span class="js-data-mule"
              data-nodes="{{ $tree->nodes->toJson() }}"
              data-links="{{ $tree->links->toJson() }}"
              data-empty=""></span>
    </span>
    </div>

    <div>
        <button class="btn btn-primary js-zoom-in">Zoom In</button>
        <button class="btn btn-primary js-zoom-out">Zoom Out</button>
    </div>
@endsection
