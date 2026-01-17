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
            <h2 class="card-title">Create Vehicle (AI Assist)</h2>
            <p class="card-subtitle">Select a region, start typing a make, then pick a model.</p>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Region</label>
                <select id="aiLocation" class="form-control">
                    <option value="AU">Australia</option>
                    <option value="NZ">New Zealand</option>
                    <option value="US">United States</option>
                    <option value="RSA">South Africa</option>
                    <option value="UK">United Kingdom</option>
                </select>
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
    color: #0A2A4D;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 16px;
    line-height: 1;
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
    const locationSelect = document.getElementById('aiLocation');
    const makeList = document.getElementById('aiMakeList');
    const modelList = document.getElementById('aiModelList');
    const trimList = document.getElementById('aiTrimList');
    const makeStatus = document.getElementById('aiMakeStatus');
    const modelStatus = document.getElementById('aiModelStatus');
    const trimStatus = document.getElementById('aiTrimStatus');
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let currentMake = '';
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

    async function fetchMakes() {
        const query = (makeInput.value || '').trim();
        if (query.length < 2) {
            clearList(makeList);
            setStatus(makeStatus, 'Type at least 2 characters.');
            return;
        }
        setStatus(makeStatus, 'Loading makes...');
        const data = await postJson('/app/sharpfleet/admin/vehicles-ai-test/makes', {
            query,
            location: locationSelect.value,
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
            location: locationSelect.value,
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

    async function fetchTrims() {
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
            location: locationSelect.value,
        });
        setStatus(trimStatus, data.items.length ? 'Pick a variant.' : 'No variants found.');
        renderList(trimList, data.items, (item) => {
            trimInput.value = item;
            clearList(trimList);
            setStatus(trimStatus, 'Variant selected.');
        });
    }

    function debounce(fn, delay, timerRef) {
        return function () {
            clearTimeout(timerRef.value);
            timerRef.value = setTimeout(fn, delay);
        };
    }

    const makeTimerRef = { value: null };
    const modelTimerRef = { value: null };
    const trimTimerRef = { value: null };

    makeInput.addEventListener('input', debounce(fetchMakes, 300, makeTimerRef));
    modelInput.addEventListener('input', debounce(fetchModels, 300, modelTimerRef));
    trimInput.addEventListener('input', debounce(fetchTrims, 300, trimTimerRef));

    locationSelect.addEventListener('change', () => {
        clearAll();
        if (makeInput.value.trim().length >= 2) {
            fetchMakes();
        }
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
            }
        });
    });
})();
</script>
@endsection
