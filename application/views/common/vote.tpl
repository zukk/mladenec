{capture assign=vote}{$votes[$c->id]|default:''}{/capture}
<blockquote{if $vote} class="done"{/if}>
    Отзыв полезен? <a class="do ok" rel="{$c->id}">Да</a> ({$c->vote_ok}) | <a class="do no" rel="{$c->id}">Нет</a> ({$c->vote_no})
</blockquote>