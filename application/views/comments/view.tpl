<div id="breadcrumb">    
    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" itemref="breadcrumb-1">
        <a href="/" itemprop="url" title="На главную"><span itemprop="title">Главная</span></a>
    </span>
    &rarr;
    <span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-1">
        <a href="{$theme->get_list_link()}" title="Отзывы о нашем магазине"><span itemprop="title">Отзывы</span></a>
    </span>
    &rarr;
    <span>{$theme->name}</span>
</div>

<div>
	{if not $allowAnswer}
		<a href="/about/review/add" class="comment butt" rel="ajax" data-fancybox-type="ajax">Оставить отзыв</a>
	{/if}
    <h1 class="w500">{$theme->name}</h1>
    <div class="livecomments">
        <div class="livecomment_box">
			{foreach from=$data item=c}
             <div class="livecomment">
                 <a>{$theme->user_name}</a>
                 <small>{$c['comment']->date|date_format:'%d-%m-%y %H:%M'}</small>
                 <strong>{$theme->name}</strong>
                 {$c['comment']->text|nl2br}
                 <i></i>
             </div>
			{foreach from=$c['answers'] item=answer}
             <div class="liveanswer">
                 <div class="liveanswer_body open">{$answer->answer}</div>
                 <i></i>
             </div>
             <small>{$answer->get_answer_by()}</small>
             <div class="cb" style='height: 10px;'></div>
			{/foreach}
			{/foreach}
	{if $allowAnswer}
		<a data-url='/about/review/add' href="#?theme={$theme->id}" class="comment butt appendhash fl" rel="ajax" data-fancybox-type="ajax">Ответить</a>
	{/if}
        </div>
    </div>

</div>