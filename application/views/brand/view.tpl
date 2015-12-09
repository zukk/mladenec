<div id="breadcrumb">
    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" itemref="breadcrumb-1">
        <a href="/" itemprop="url"><span itemprop="title">Главная</span></a>
    </span>
    &rarr;
    <span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-1" itemref="breadcrumb-2">
        <a href="{Route::url('brands')}" itemprop="url"><span itemprop="title">Бренды</span></a>
    </span>
    &rarr;
    <span>{$brand->name}</span>
    <i></i>
</div>


<div class="yell">
    <h1>{$brand->name}</h1>
</div>

<div>{$brand->text}</div>

{$search_result}


