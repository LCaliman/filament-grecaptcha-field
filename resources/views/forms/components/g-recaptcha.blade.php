<script>
    document.addEventListener('livewire:load', function() {
        @this.on('resetCaptcha', () => {
            const componentId = '{{ method_exists($this, 'id') ? $this->id() : $this->id }}';
            const widgetId = window.grecaptchaWidgets[componentId];
            if (widgetId !== undefined && window.grecaptcha && typeof window.grecaptcha.reset ===
                'function') {
                window.grecaptcha.reset(widgetId);
            }
        });
    });

    document.addEventListener('livewire:initialized', () => {
        @this.on('resetCaptcha', () => {
            const componentId = '{{ method_exists($this, 'id') ? $this->id() : $this->id }}';
            const widgetId = window.grecaptchaWidgets[componentId];
            if (widgetId !== undefined && window.grecaptcha && typeof window.grecaptcha.reset ===
                'function') {
                window.grecaptcha.reset(widgetId);
            }
        });
    });

    // Unique callback function for this specific component
    window.recaptchaCallback_{{ method_exists($this, 'id') ? $this->id() : $this->id }} = (token) => {
        const componentId = '{{ method_exists($this, 'id') ? $this->id() : $this->id }}';

        // Only proceed if we have a valid token
        if (token && token.length > 0) {
            const livewireComponent = window.Livewire?.find(componentId);
            if (livewireComponent) {
                try {
                    // Set the token in the Livewire component
                    livewireComponent.set('{{ $getStatePath() }}', token, true);
                } catch (error) {
                    console.error('Error setting reCAPTCHA token:', error);
                }
            } else {
                console.error('Livewire component not found for reCAPTCHA:', componentId);
            }
        }
    };

    // Function to register widget ID after reCAPTCHA renders
    window.registerRecaptchaWidget_{{ method_exists($this, 'id') ? $this->id() : $this->id }} = (widgetId) => {
        window.grecaptchaWidgets['{{ method_exists($this, 'id') ? $this->id() : $this->id }}'] = widgetId;
    };
</script>

<x-dynamic-component :component="$getFieldWrapperView()" :field="method_exists($this, 'id') ? $field : null" :component="$getFieldWrapperView()" :id="$getId()" :label="$getLabel()"
    :label-sr-only="$isLabelHidden()" :hint="$getHint()" :hint-color="$getHintColor()" :hint-icon="$getHintIcon()" :required="$isRequired()" :state-path="$getStatePath()">
    <div x-data="{
        state: $wire.entangle('{{ $getStatePath() }}').defer,
        componentId: '{{ method_exists($this, 'id') ? $this->id() : $this->id }}'
    }"
        x-on:reset-recaptcha="
            const widgetId = window.grecaptchaWidgets[componentId];
            if (widgetId !== undefined && window.grecaptcha) {
                window.grecaptcha.reset(widgetId);
            }
        "
        wire:ignore
        x-on:next-wizard-step.window="
            const widgetId = window.grecaptchaWidgets[componentId];
            if (widgetId !== undefined && window.grecaptcha) {
                window.grecaptcha.reset(widgetId);
            }
        "
        x-on:expand-concealing-component.window="
            // Only reset this specific reCAPTCHA if THIS component has a validation error
            error = $el.parentElement.querySelector('[data-validation-error]');
            if (error) {
                const widgetId = window.grecaptchaWidgets[componentId];
                if (widgetId !== undefined && window.grecaptcha) {
                    window.grecaptcha.reset(widgetId);
                }
                setTimeout(() => $el.scrollIntoView({ behavior: 'smooth', block: 'start', inline: 'start' }), 200);
            }
        "
        data-component-id="{{ method_exists($this, 'id') ? $this->id() : $this->id }}">

        <div id="recaptcha-{{ method_exists($this, 'id') ? $this->id() : $this->id }}" wire:ignore>
            {!! NoCaptcha::renderJs(app()->getLocale()) !!}
            {!! NoCaptcha::display([
                'data-callback' => 'recaptchaCallback_' . (method_exists($this, 'id') ? $this->id() : $this->id),
                'id' => 'recaptcha-widget-' . (method_exists($this, 'id') ? $this->id() : $this->id),
            ]) !!}
        </div>

        {{-- <script>
            // Register widget ID when this component loads
            (function() {
                const componentId = '{{ method_exists($this, 'id') ? $this->id() : $this->id }}';

                function registerWidget() {
                    if (window.grecaptcha && window.grecaptcha.ready) {
                        window.grecaptcha.ready(function() {
                            const container = document.querySelector('[data-component-id="' + componentId + '"]');
                            if (container) {
                                const recaptchaWidget = container.querySelector('.g-recaptcha');
                                if (recaptchaWidget) {
                                    // Method 1: Try to get from existing attribute
                                    let widgetId = recaptchaWidget.getAttribute('data-recaptcha-id');
                                    if (widgetId) {
                                        window.grecaptchaWidgets[componentId] = parseInt(widgetId);
                                        return;
                                    }

                                    // Method 2: Watch for the attribute to be added
                                    const observer = new MutationObserver(function(mutations) {
                                        mutations.forEach(function(mutation) {
                                            if (mutation.type === 'attributes' && mutation
                                                .attributeName === 'data-recaptcha-id') {
                                                const widgetId = parseInt(recaptchaWidget
                                                    .getAttribute('data-recaptcha-id'));
                                                if (!isNaN(widgetId)) {
                                                    window.grecaptchaWidgets[componentId] =
                                                        widgetId;
                                                    observer.disconnect();
                                                }
                                            }
                                        });
                                    });

                                    observer.observe(recaptchaWidget, {
                                        attributes: true,
                                        attributeFilter: ['data-recaptcha-id']
                                    });

                                    // Method 3: Fallback - try to find by checking all widgets
                                    setTimeout(function() {
                                        if (window.grecaptchaWidgets[componentId] === undefined) {
                                            // Look for all g-recaptcha elements and find our index
                                            const allRecaptchas = document.querySelectorAll('.g-recaptcha');
                                            for (let i = 0; i < allRecaptchas.length; i++) {
                                                if (allRecaptchas[i] === recaptchaWidget) {
                                                    window.grecaptchaWidgets[componentId] = i;
                                                    break;
                                                }
                                            }
                                        }
                                        observer.disconnect();
                                    }, 1000);
                                }
                            }
                        });
                    }
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', registerWidget);
                } else {
                    registerWidget();
                }
            })();
        </script> --}}
    </div>
</x-dynamic-component>
