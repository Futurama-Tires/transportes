// resources/js/vehiculos/index.js

// Importa el CSS específico de la galería para que Vite lo procese:
import '../../css/vehiculos/gallery.css';

document.addEventListener('DOMContentLoaded', () => {
  // Contenedor con data-attrs definido en el Blade
  const appRoot = document.getElementById('vehiculos-app');
  if (!appRoot) return;

  const basePhotoUrl = appRoot.dataset.basePhoto || '';
  const baseVehUrl   = appRoot.dataset.baseVeh   || '';
  if (!basePhotoUrl || !baseVehUrl) return;

  // ====== SELECTORES (modal detalle) ======
  const vehicleModalEl   = document.getElementById('vehicleModal');
  if (!vehicleModalEl) return;

  const titleEl          = vehicleModalEl.querySelector('#vehicleModalLabel');
  const subtitleEl       = vehicleModalEl.querySelector('#vehicleModalSubtitle');
  const managePhotosLink = vehicleModalEl.querySelector('#managePhotosLink');
  const addTankLink      = vehicleModalEl.querySelector('#addTankLink');
  const editVehicleLink  = vehicleModalEl.querySelector('#editVehicleLink');
  const photosGrid       = vehicleModalEl.querySelector('#photosGrid');
  const photosEmpty      = vehicleModalEl.querySelector('#photosEmpty');
  const openGalleryBtn   = vehicleModalEl.querySelector('#openGalleryBtn');
  const tanksTbody       = vehicleModalEl.querySelector('#tanksTbody');

  // ====== SELECTORES (modal galería) ======
  const galleryModalEl    = document.getElementById('galleryModal');
  const galleryCarouselEl = document.getElementById('galleryCarousel');
  const galleryInner      = document.getElementById('galleryInner');
  const galleryThumbs     = document.getElementById('galleryThumbs');

  // Helpers de formateo
  const fmt      = v => (v ?? '') !== '' ? v : '—';
  const fmtDate  = v => { if(!v) return '—'; const d = new Date(v); return isNaN(d) ? v : d.toLocaleDateString('es-MX',{year:'numeric',month:'2-digit',day:'2-digit'}); };
  const fmtNum   = n => (n===''||n==null) ? '—' : (isNaN(+n)?'—':(+n).toLocaleString('es-MX',{minimumFractionDigits:2,maximumFractionDigits:2}));
  const fmtMoney = n => (n===''||n==null) ? '—' : (isNaN(+n)?'—':(+n).toLocaleString('es-MX',{style:'currency',currency:'MXN'}));
  const fmtInt   = n => (n===''||n==null) ? '—' : (isNaN(+n)?'—':Math.trunc(+n).toLocaleString('es-MX'));

  const getCarouselCtor = () => (window.bootstrap?.Carousel) || window.Carousel || null;
  const getModalCtor    = () => (window.bootstrap?.Modal)    || window.Modal    || null;

  let currentVeh = null;

  function fillModal(veh){
    currentVeh = veh || {};

    if (titleEl)    titleEl.textContent    = veh.unidad ? `Unidad: ${veh.unidad}` : `Vehículo #${veh.id ?? ''}`;
    if (subtitleEl) subtitleEl.textContent = veh.placa ? `Placa: ${veh.placa}` : '';

    const fields = {
      id: fmt(veh.id),
      unidad: fmt(veh.unidad),
      placa: fmt(veh.placa),
      serie: fmt(veh.serie),
      marca: fmt(veh.marca),
      anio: fmt(veh.anio),
      propietario: fmt(veh.propietario),
      ubicacion: fmt(veh.ubicacion),
      estado: fmt(veh.estado),
      motor: fmt(veh.motor),
      kilometros: fmtInt(veh.kilometros),
      tarjeta: fmt(veh.tarjeta),
      nip: fmt(veh.nip),
      fec_vencimiento: fmtDate(veh.fec_vencimiento),
      vencimiento_t_circulacion: fmtDate(veh.vencimiento_t_circulacion),
      cambio_placas: fmtDate(veh.cambio_placas),
      poliza_hdi: fmt(veh.poliza_hdi),
      poliza_latino: fmt(veh.poliza_latino),
      poliza_qualitas: fmt(veh.poliza_qualitas),
    };

    Object.entries(fields).forEach(([k,v])=>{
      const el = vehicleModalEl.querySelector(`[data-v="${k}"]`);
      if(el) el.textContent = v;
    });

    const id = veh.id;
    if (id && managePhotosLink) managePhotosLink.href = `${baseVehUrl}/${id}/fotos`;
    if (id && addTankLink)      addTankLink.href      = `${baseVehUrl}/${id}/tanques/create`;
    if (id && editVehicleLink)  editVehicleLink.href  = `${baseVehUrl}/${id}/edit`;

    // Miniaturas en el modal de detalle
    if (photosGrid && photosEmpty && openGalleryBtn) {
      photosGrid.innerHTML = '';
      const fotos = Array.isArray(veh.fotos) ? veh.fotos : [];
      if(!fotos.length){
        photosEmpty.classList.remove('d-none');
        openGalleryBtn.classList.add('d-none');
      } else {
        photosEmpty.classList.add('d-none');
        openGalleryBtn.classList.remove('d-none');
        fotos.forEach((f, idx)=>{
          const col = document.createElement('div');
          col.className = 'col-6 col-sm-4 col-md-3';
          col.innerHTML = `
            <a href="#" class="card card-link" data-gallery-index="${idx}">
              <div class="img-responsive img-responsive-4x3 card-img-top"
                   style="background-image: url('${basePhotoUrl}/${f.id}')"></div>
            </a>`;
          photosGrid.appendChild(col);
        });
      }
    }

    // Tanques
    if (tanksTbody) {
      tanksTbody.innerHTML = '';
      const tanques = Array.isArray(veh.tanques) ? veh.tanques : [];
      if(!tanques.length){
        const tr = document.createElement('tr');
        tr.innerHTML = `<td colspan="6" class="text-secondary small">Este vehículo no tiene tanques.</td>`;
        tanksTbody.appendChild(tr);
      } else {
        tanques.forEach(t=>{
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${fmt(t.numero_tanque)}</td>
            <td>${fmt(t.tipo_combustible)}</td>
            <td>${fmtNum(t.capacidad_litros)}</td>
            <td>${fmtNum(t.rendimiento_estimado)}</td>
            <td>${fmtNum(t.km_recorre)}</td>
            <td>${fmtMoney(t.costo_tanque_lleno)}</td>`;
          tanksTbody.appendChild(tr);
        });
      }
    }
  }

  function updateThumbsActive(i){
    if (!galleryThumbs) return;
    [...galleryThumbs.querySelectorAll('.thumb')].forEach((el,idx)=>{
      el.classList.toggle('active', idx === i);
    });
  }

  function buildCarouselSlides(startIndex = 0){
    if (!galleryInner || !galleryThumbs) return;

    galleryInner.innerHTML = '';
    galleryThumbs.innerHTML = '';

    const fotos = Array.isArray(currentVeh?.fotos) ? currentVeh.fotos : [];
    fotos.forEach((f,i)=>{
      const item = document.createElement('div');
      item.className = 'carousel-item' + (i===startIndex ? ' active' : '');
      item.dataset.index = i;
      item.innerHTML = `<img src="${basePhotoUrl}/${f.id}" class="lightbox-img" alt="Foto ${i+1}">`;
      galleryInner.appendChild(item);

      const th = document.createElement('button');
      th.type = 'button';
      th.className = 'thumb' + (i===startIndex ? ' active' : '');
      th.dataset.index = i;
      th.innerHTML = `<img src="${basePhotoUrl}/${f.id}" alt="Miniatura ${i+1}">`;
      th.addEventListener('click', () => {
        const Carousel = getCarouselCtor();
        if (!Carousel || !galleryCarouselEl) return;
        const car = Carousel.getInstance(galleryCarouselEl);
        car?.to(i);
        updateThumbsActive(i);
      });
      galleryThumbs.appendChild(th);
    });

    const Carousel = getCarouselCtor();
    if (Carousel && galleryCarouselEl) {
      const existing = Carousel.getInstance(galleryCarouselEl);
      existing?.dispose();
      const carousel = new Carousel(galleryCarouselEl, {
        interval: false, ride: false, wrap: true, keyboard: true, touch: true
      });

      galleryCarouselEl.addEventListener('slid.bs.carousel', () => {
        const idx = [...galleryInner.children].findIndex(el => el.classList.contains('active'));
        updateThumbsActive(idx);
      }, { passive: true });

      if (startIndex > 0) carousel.to(startIndex);
    }
  }

  function openGallery(startIndex = 0){
    if(!currentVeh || !currentVeh.fotos || !currentVeh.fotos.length) return;
    buildCarouselSlides(startIndex);
    const Modal = getModalCtor();
    if (!Modal || !galleryModalEl) return;
    const modal = new Modal(galleryModalEl);
    modal.show();
  }

  // Clic en miniaturas del detalle
  if (photosGrid) {
    photosGrid.addEventListener('click', (e) => {
      const a = e.target.closest('[data-gallery-index]');
      if (!a) return;
      e.preventDefault();
      const idx = parseInt(a.getAttribute('data-gallery-index'), 10) || 0;
      openGallery(idx);
    });
  }

  // Botón "Ver galería"
  if (openGalleryBtn) {
    openGalleryBtn.addEventListener('click', () => openGallery(0));
  }

  // Delegación: botón "Ver" (inyecta data en el modal)
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-view-veh');
    if (!btn) return;
    try {
      const payload = JSON.parse(btn.getAttribute('data-veh') || '{}');
      fillModal(payload);
    } catch (err) {
      console.error('JSON inválido en data-veh:', err);
    }
  });
});
