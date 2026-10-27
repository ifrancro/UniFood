<div {{ $attributes->merge(['class' => 'food-scene']) }} aria-hidden="true">
    <div class="scene-circle"></div>
    <span class="scene-doodle doodle-one">✦</span><span class="scene-doodle doodle-two">✧</span>
    <div class="scene-coffee"><img src="{{ asset('images/menu/capuchino.svg') }}" alt="" width="340" height="280"><div class="steam"><i></i><i></i><i></i></div></div>
    <img class="scene-pastry" src="{{ asset('images/menu/saltena.svg') }}" alt="" width="340" height="280">
    <div class="scene-note"><x-icon name="check" :size="16"/> Tú pides. Nosotros preparamos.</div>
</div>
