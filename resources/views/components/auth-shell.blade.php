<section class="auth-layout" data-reveal>
    <div class="auth-story">
        <span class="eyebrow"><span class="tiny-dot"></span> TU TIENDA EN UNIVALLE</span>
        <h1>Más tiempo para ti.<br><em>Menos tiempo<br>en la fila.</em></h1>
        <p>El café de siempre. Tu antojo favorito. Ahora puedes pedirlos desde cualquier lugar del campus.</p>
        <div class="auth-feature"><x-icon name="check" :size="16"/> Elige, pide y recoge en UniFood</div>
        <div class="auth-feature"><x-icon name="check" :size="16"/> Paga presencialmente al recoger</div>
        <x-food-scene/>
    </div>
    <div class="auth-form-panel">{{ $slot }}</div>
</section>
