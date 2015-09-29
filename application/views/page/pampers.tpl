<h1>Анкета Pampers</h1>

{if ! empty($p)}
    <div class="cl">
        <h2>Спасибо</h2>
        <p>Ваша анкета принята.</p>
    </div>
{else}
    <form action="" method="post" class="cols" id="pampers_form">
        <label class="l" for="name">ФИО мамы:&nbsp;<sup>*</sup></label>
        <input id="name" name="name" class="txt {if ! empty($errors.name)}error" error="{$errors.name}{/if}" value="{$smarty.post.name|default:''}" />

        <label class="l" for="weight">Вес малыша:&nbsp;<sup>*</sup></label>
        <input id="weight" name="weight" class="txt {if ! empty($errors.weight)}error" error="{$errors.weight}{/if}" value="{$smarty.post.weight|default:''}" />

        <label class="l" for="age">Возраст малыша:&nbsp;<sup>*</sup></label>
        <input id="age" name="age" class="txt {if ! empty($errors.age)}error" error="{$errors.age}{/if}" value="{$smarty.post.age|default:''}" />

        <label class="l" for="index">Почтовый индекс:&nbsp;<sup>*</sup></label>
        <input maxlength="6" id="index" name="index" class="txt {if ! empty($errors.index)}error" error="{$errors.index}{/if}" value="{$smarty.post.index|default:''}" />

        <label class="l" for="address">Почтовый адрес:&nbsp;<sup>*</sup></label>
        <textarea id="address" name="address" class="txt {if ! empty($errors.address)}error" error="{$errors.address}{/if}" rows="5">{$smarty.post.address|default:''}</textarea>

        <label class="l" for="phone">Телефон:&nbsp;<sup>*</sup></label>
        <input id="phone" type="tel" name="phone" class="txt {if ! empty($errors.phone)}error" error="{$errors.phone}{/if}" value="{$smarty.post.phone|default:''}" />

        <label class="l" for="email">E-mail:&nbsp;<sup>*</sup></label>
        <input id="email" name="email" class="txt {if ! empty($errors.email)}error" error="{$errors.email}{/if}" value="{$smarty.post.email|default:''}" />

        <label class="l" for="site">Откуда вы о нас узнали:&nbsp;<sup>*</sup></label>
        <input id="site" name="site" class="txt {if ! empty($errors.site)}error" error="{$errors.site}{/if}" value="{$smarty.post.site|default:''}" />

        <p class="cb"><br /><input type="submit" value="Отправить анкету" class="butt" name="anketa"/></p>
    </form>
{/if}