<style>
    #pampers_form {
        position:relative;
        width:650px;
        padding:30px 25px;
        height:670px;
        margin:0 auto;
        background:url(/i/pampers/anketa_bg.jpg) no-repeat;
    }
    #pampers_form form {
        position:static;
        padding-top:20px;
        margin-left:100px;
    }
    #pampers_form h1 {
        margin-left:270px;
    }
    #pampers_form .butt {
        margin: 0;
        position: absolute;
        bottom: 55px;
        left: 125px;
        width: 450px;
        height: 61px;
        background: none;
        opacity:0;
        border: none;
    }

</style>

<div id="pampers_form" {if ! empty($p)} style="height:500px;"{/if}>
    <h1>Заполните заявку на&nbsp;бесплатное получение упаковки подгузников Pampers</h1>
        <a href="http://www.mladenec-shop.ru/upload/mediafiles/1/0/6/2/4194.doc">правила акции</a>
        <form action="" method="post" class="cols">
            <div class="cl">
                {*<h2>Извините.</h2>*}
                <p>Акция по&nbsp;семплированию закончилась, спасибо Вам за&nbsp;интерес к&nbsp;продукции Pampers..</p>
            </div>

        {*if ! empty($p)}
        <div class="cl">
            <h2>Спасибо</h2>
            <p>Ваша заявка принята.</p>
        </div>
        {else}
            <label class="l" for="name">ФИО мамы:&nbsp;<sup>*</sup></label>
            <input id="name" name="name" class="txt {if ! empty($errors.name)}error" error="{$errors.name}{/if}" value="{$smarty.post.name|default:$user->name|default:''}" />

            <label class="l" for="weight">Вес малыша (кг):&nbsp;<sup>*</sup></label>
            <input id="weight" name="weight" class="txt {if ! empty($errors.weight)}error" error="{$errors.weight}{/if}" value="{$smarty.post.weight|default:''}" />

            <label class="l" for="age">Возраст малыша (мес.):&nbsp;<sup>*</sup></label>
            <input id="age" name="age" class="txt {if ! empty($errors.age)}error" error="{$errors.age}{/if}" value="{$smarty.post.age|default:''}" />

            <label class="l" for="index">Почтовый индекс:&nbsp;<sup>*</sup></label>
            <input maxlength="6" id="index" name="index" class="txt {if ! empty($errors.index)}error" error="{$errors.index}{/if}" value="{$smarty.post.index|default:''}" />

            <label class="l" for="address">Почтовый адрес:&nbsp;<sup>*</sup></label>
            <textarea id="address" name="address" class="txt {if ! empty($errors.address)}error" error="{$errors.address}{/if}" rows="5">{$smarty.post.address|default:''}</textarea>

            <label class="l" for="phone">Телефон:&nbsp;<sup>*</sup></label>
            <input id="phone" type="tel" name="phone" class="txt {if ! empty($errors.phone)}error"
                error="{$errors.phone}{/if}" value="{$smarty.post.phone|default:$user->phone|default:''}" />

            <label class="l" for="email">E-mail:&nbsp;<sup>*</sup></label>
            <input id="email" name="email" class="txt {if ! empty($errors.email)}error" error="{$errors.email}{/if}" value="{$smarty.post.email|default:$user->email|default:''}" />

            {*<label class="l" for="site">Откуда вы о нас узнали:&nbsp;<sup>*</sup></label>
            <input id="site" name="site" class="txt {if ! empty($errors.site)}error" error="{$errors.site}{/if}" value="{$smarty.post.site|default:''}" />}

            <input type="submit" value="Отправить заявку" class="butt" name="anketa" />
        {/if*}
        </form>
</div>