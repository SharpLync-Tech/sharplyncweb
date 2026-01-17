@extends('layouts.sharpfleet')

@section('title', 'Vehicle AI Test')

@section('sharpfleet-content')
<div class="sharpfleet-container">
    <div class="page-header">
        <h1 class="page-title">Vehicle AI Test</h1>
        <p class="page-description">Prototype: suggest make, then model, using AI.</p>
    </div>

    <div class="card" style="color: #0A2A4D;">
        <div class="card-header">
            <h2 class="card-title">
                Create Vehicle
                <span class="ai-badge" aria-label="AI">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 3l1.6 4.8L18 9.4l-4.4 1.6L12 16l-1.6-5L6 9.4l4.4-1.6L12 3z"></path>
                        <path d="M6 16l.9 2.6L9.5 20l-2.6.9L6 23l-.9-2.6L2.5 20l2.6-.9L6 16z"></path>
                        <path d="M18 14l.7 2L20 17l-2 .7L17 20l-.7-2L14 17l2-.7L18 14z"></path>
                    </svg>
                </span>
            </h2>
            <p class="card-subtitle">Select a country, start typing a make, then pick a model.</p>
            <p class="form-hint" style="color: #6b7280; margin-top: 8px;">💡 Tip: Start typing and we’ll do the rest</p>
        </div>
        <div class="card-body">
            <div class="form-group" style="display:none;">
                <label class="form-label">Quick entry</label>
                <div class="ai-input-wrap">
                    <input id="aiFreeTextInput" class="form-control" type="text" placeholder="e.g. Toyota Camry 2020 GL">
                    <button type="button" class="ai-clear-btn" data-clear="free" aria-label="Clear quick entry">×</button>
                </div>
                <div id="aiFreeTextStatus" class="form-hint" style="color: #6b7280;"></div>
            </div>

            <div class="form-group">
                <label class="form-label">Country</label>
                <div class="ai-input-wrap">
                    <input id="aiLocationInput" class="form-control" type="text" placeholder="Start typing a country">
                    <button type="button" class="ai-clear-btn" data-clear="country" aria-label="Clear country">×</button>
                </div>
                <div id="aiLocationStatus" class="form-hint" style="color: #6b7280;"></div>
                <div id="aiLocationList" class="ai-list"></div>
            </div>

            <div class="form-group">
                <label class="form-label">Make</label>
                <div class="ai-input-wrap">
                    <input id="aiMakeInput" class="form-control" type="text" placeholder="Start typing a make (e.g., Toyota)">
                    <button type="button" class="ai-clear-btn" data-clear="make" aria-label="Clear make">×</button>
                </div>
                <div id="aiMakeStatus" class="form-hint" style="color: #6b7280;"></div>
                <div id="aiMakeList" class="ai-list"></div>
            </div>

            <div class="form-group">
                <label class="form-label">Model</label>
                <div class="ai-input-wrap">
                    <input id="aiModelInput" class="form-control" type="text" placeholder="Start typing a model">
                    <button type="button" class="ai-clear-btn" data-clear="model" aria-label="Clear model">×</button>
                </div>
                <div id="aiModelStatus" class="form-hint" style="color: #6b7280;"></div>
                <div id="aiModelList" class="ai-list"></div>
            </div>

            <div class="form-group">
                <label class="form-label">Variant</label>
                <div class="ai-input-wrap">
                    <input id="aiTrimInput" class="form-control" type="text" placeholder="Start typing a variant">
                    <button type="button" class="ai-clear-btn" data-clear="trim" aria-label="Clear variant">×</button>
                </div>
                <div id="aiTrimStatus" class="form-hint" style="color: #6b7280;"></div>
                <div id="aiTrimList" class="ai-list"></div>
            </div>
        </div>
    </div>
</div>

<style>
.ai-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-left: 8px;
    width: 26px;
    height: 26px;
    border-radius: 999px;
    background: rgba(44, 191, 174, 0.12);
    border: 1px solid rgba(44, 191, 174, 0.35);
}

.ai-badge svg {
    width: 16px;
    height: 16px;
    fill: #2CBFAE;
}

.ai-list {
    margin-top: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.ai-input-wrap {
    position: relative;
}

.ai-input-wrap .form-control {
    padding-right: 38px;
}

.ai-clear-btn {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: rgba(10, 42, 77, 0.08);
    color: transparent;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 16px;
    line-height: 1;
}

.ai-clear-btn::before {
    content: "x";
    color: #0A2A4D;
    font-weight: 700;
}

.ai-clear-btn:hover {
    background: rgba(10, 42, 77, 0.18);
}

.ai-chip {
    border: 1px solid rgba(44, 191, 174, 0.35);
    background: rgba(44, 191, 174, 0.08);
    color: #0A2A4D;
    padding: 6px 10px;
    border-radius: 999px;
    cursor: pointer;
    font-size: 14px;
}

.ai-chip:hover {
    background: rgba(44, 191, 174, 0.18);
}
</style>

<script>
(function () {
    const makeInput = document.getElementById('aiMakeInput');
    const modelInput = document.getElementById('aiModelInput');
    const trimInput = document.getElementById('aiTrimInput');
    const locationInput = document.getElementById('aiLocationInput');
    const locationList = document.getElementById('aiLocationList');
    const makeList = document.getElementById('aiMakeList');
    const modelList = document.getElementById('aiModelList');
    const trimList = document.getElementById('aiTrimList');
    const locationStatus = document.getElementById('aiLocationStatus');
    const makeStatus = document.getElementById('aiMakeStatus');
    const modelStatus = document.getElementById('aiModelStatus');
    const trimStatus = document.getElementById('aiTrimStatus');
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let currentMake = '';
    const tipLine = document.querySelector('.card-header .form-hint');
    if (tipLine) {
        tipLine.textContent = "Tip: Start typing and we'll do the rest";
    }

    let locationTimer = null;
    let makeTimer = null;
    let modelTimer = null;
    let trimTimer = null;

    function setStatus(el, text) {
        if (!el) return;
        el.textContent = text;
    }

    function clearList(el) {
        if (el) el.innerHTML = '';
    }

    function clearModels() {
        modelInput.value = '';
        trimInput.value = '';
        clearList(modelList);
        clearList(trimList);
        setStatus(modelStatus, '');
        setStatus(trimStatus, '');
    }

    function clearAll() {
        makeInput.value = '';
        currentMake = '';
        clearList(makeList);
        setStatus(makeStatus, '');
        clearModels();
    }

    function renderList(el, items, onPick) {
        clearList(el);
        items.forEach(item => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ai-chip';
            btn.textContent = item;
            btn.addEventListener('click', () => onPick(item));
            el.appendChild(btn);
        });
    }

    async function postJson(url, payload) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });
        if (!res.ok) return { items: [] };
        return res.json();
    }

    function getLocation() {
        return (locationInput.value || '').trim();
    }

    async function fetchCountries() {
        const query = getLocation();
        if (query.length < 2) {
            clearList(locationList);
            setStatus(locationStatus, 'Type at least 2 characters.');
            return;
        }
        setStatus(locationStatus, 'Loading countries...');
        const data = await postJson('/app/sharpfleet/admin/vehicles-ai-test/countries', {
            query,
        });
        setStatus(locationStatus, data.items.length ? 'Pick a country.' : 'No countries found.');
        renderList(locationList, data.items, (item) => {
            locationInput.value = item;
            clearList(locationList);
            setStatus(locationStatus, 'Country selected.');
            clearAll();
            locationInput.value = item;
            if (makeInput.value.trim().length >= 2) {
                fetchMakes();
            }
        });
    }

    async function fetchMakes() {
        const query = (makeInput.value || '').trim();
        const location = getLocation();
        if (!location) {
            clearList(makeList);
            setStatus(makeStatus, 'Select a country first.');
            return;
        }
        if (query.length < 2) {
            clearList(makeList);
            setStatus(makeStatus, 'Type at least 2 characters.');
            return;
        }
        setStatus(makeStatus, 'Loading makes...');
        const data = await postJson('/app/sharpfleet/admin/vehicles-ai-test/makes', {
            query,
            location,
        });
        setStatus(makeStatus, data.items.length ? 'Pick a make.' : 'No makes found.');
        renderList(makeList, data.items, (item) => {
            currentMake = item;
            makeInput.value = item;
            clearList(makeList);
            setStatus(makeStatus, 'Make selected.');
            clearModels();
            modelInput.focus();
            fetchModels();
        });
    }

    async function fetchModels() {
        const query = (modelInput.value || '').trim();
        if (!currentMake) {
            clearList(modelList);
            setStatus(modelStatus, 'Select a make first.');
            return;
        }
        setStatus(modelStatus, 'Loading models...');
        const data = await postJson('/app/sharpfleet/admin/vehicles-ai-test/models', {
            make: currentMake,
            query,
            location: getLocation(),
        });
        setStatus(modelStatus, data.items.length ? 'Pick a model.' : 'No models found.');
        renderList(modelList, data.items, (item) => {
            modelInput.value = item;
            clearList(modelList);
            setStatus(modelStatus, 'Model selected.');
            trimInput.value = '';
            clearList(trimList);
            setStatus(trimStatus, '');
            trimInput.focus();
            fetchTrims();
        });
    }

    async function fetchTrims(autoPickFirst = false) {
        const query = (trimInput.value || '').trim();
        if (!currentMake || !modelInput.value.trim()) {
            clearList(trimList);
            setStatus(trimStatus, 'Select a make and model first.');
            return;
        }
        setStatus(trimStatus, 'Loading variants...');
        const data = await postJson('/app/sharpfleet/admin/vehicles-ai-test/trims', {
            make: currentMake,
            model: modelInput.value.trim(),
            query,
            location: getLocation(),
        });
        setStatus(trimStatus, data.items.length ? 'Pick a variant.' : 'No variants found.');
        renderList(trimList, data.items, (item) => {
            trimInput.value = item;
            clearList(trimList);
            setStatus(trimStatus, 'Variant selected.');
        });

        if (autoPickFirst && data.items.length > 0) {
            trimInput.value = data.items[0];
            clearList(trimList);
            setStatus(trimStatus, 'Variant suggested.');
        }
    }

    function debounce(fn, delay, timerRef) {
        return function () {
            clearTimeout(timerRef.value);
            timerRef.value = setTimeout(fn, delay);
        };
    }

    const locationTimerRef = { value: null };
    const makeTimerRef = { value: null };
    const modelTimerRef = { value: null };
    const trimTimerRef = { value: null };

    locationInput.addEventListener('input', debounce(fetchCountries, 300, locationTimerRef));
    makeInput.addEventListener('input', debounce(fetchMakes, 300, makeTimerRef));
    modelInput.addEventListener('input', debounce(fetchModels, 300, modelTimerRef));
    trimInput.addEventListener('input', debounce(fetchTrims, 300, trimTimerRef));

    locationInput.addEventListener('change', () => {
        clearAll();
    });

    makeInput.addEventListener('change', () => {
        currentMake = makeInput.value.trim();
        clearModels();
    });

    document.querySelectorAll('.ai-clear-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-clear');
            if (target === 'make') {
                clearAll();
            } else if (target === 'model') {
                clearModels();
            } else if (target === 'trim') {
                trimInput.value = '';
                clearList(trimList);
                setStatus(trimStatus, '');
            } else if (target === 'country') {
                locationInput.value = '';
                clearList(locationList);
                setStatus(locationStatus, '');
                clearAll();
        });
    });
})();
</script>
@endsection
