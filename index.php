<?php

$tema = isset($_GET['tema']) ? $_GET['tema'] : 'oscuro';
$tema = $tema === 'claro' ? 'claro' : 'oscuro';

function url_con_tema(string $tema_deseado): string
{
    $parametros = $_GET;
    $parametros['tema'] = $tema_deseado;
    return '?' . http_build_query($parametros);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Billie Eilish · Álbumes y canciones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">

    <!-- SweetAlert2 via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="tema-<?php echo htmlspecialchars($tema, ENT_QUOTES, 'UTF-8'); ?>">

    <header class="billie-eilish-header">
        <div class="billie-eilish-header-overlay">
            <div class="billie-eilish-header-content">
                <h1 class="billie-eilish-logo">Billie Eilish · Discografía</h1>
                <p class="billie-eilish-subtitle">
                    Explorá álbumes y canciones, filtrá por disco y gestioná tus canciones favoritas.
                </p>

                <div class="tema-switcher">
                    <span>Tema: </span>
                    <a href="<?php echo url_con_tema('oscuro'); ?>"
                        class="tema-btn <?php echo $tema === 'oscuro' ? 'active' : ''; ?>">
                        Oscuro
                    </a>
                    <a href="<?php echo url_con_tema('claro'); ?>"
                        class="tema-btn <?php echo $tema === 'claro' ? 'active' : ''; ?>">
                        Claro
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="container">

        <!-- Filtros -->
        <section class="filters">
            <form id="formularioFiltros" class="filter-form" autocomplete="off">
                <div class="filter-group">
                    <label for="campoBusqueda">Buscar por canción</label>
                    <input type="text"
                        id="campoBusqueda"
                        name="busqueda"
                        placeholder="Ej: bad guy, NDA, LUNCH...">
                </div>

                <div class="filter-group">
                    <label for="selectAlbum">Filtrar por álbum</label>
                    <select id="selectAlbum" name="album">
                        <option value="">Todos los álbumes</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-primary">
                        Aplicar filtros
                    </button>
                    <button type="button" id="botonLimpiar" class="btn-secondary">
                        Limpiar
                    </button>
                </div>
            </form>
        </section>

        <!-- Formulario de alta / edicion -->
        <section class="filters" style="margin-top: 8px;">
            <form id="formularioCancion" class="filter-form" method="POST" autocomplete="off">
                <input type="hidden" id="campoId" name="id" value="">
                <input type="hidden" id="campoAccion" name="accion" value="crear">

                <div class="filter-group">
                    <label for="campoTitulo">Título de la canción</label>
                    <input type="text" id="campoTitulo" name="titulo" required>
                </div>

                <div class="filter-group">
                    <label for="campoAlbum">Álbum</label>
                    <input type="text" id="campoAlbum" name="album" required>
                </div>

                <div class="filter-group">
                    <label for="campoDescripcion">Descripción</label>
                    <textarea id="campoDescripcion" name="descripcion" rows="2" required></textarea>
                </div>

                <div class="filter-group">
                    <label for="campoImagen">URL de la imagen</label>
                    <input type="text" id="campoImagen" name="imagen" required>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-primary" id="botonGuardar">
                        Agregar canción
                    </button>
                    <button type="button" class="btn-secondary" id="botonCancelarEdicion" style="display: none;">
                        Cancelar edición
                    </button>
                </div>
            </form>
        </section>

        <!-- Resultados -->
        <section>
            <h2 class="section-title">Canciones</h2>
            <p id="textoResultados" class="results-info">Cargando canciones...</p>
            <div id="contenedorTarjetas" class="cards-grid"></div>

            <!-- Paginacion -->
            <div id="contenedorPaginacion" class="paginacion"></div>
        </section>

    </main>

    <footer class="billie-eilish-footer">
        <p>Parcial-2-p2-acn2bv-belgorodsky-mato-solis - 2025</p>
    </footer>

    <script>
        const urlApi = 'api.php';

        const contenedorTarjetas = document.getElementById('contenedorTarjetas');
        const textoResultados = document.getElementById('textoResultados');
        const selectAlbum = document.getElementById('selectAlbum');
        const campoBusqueda = document.getElementById('campoBusqueda');
        const formularioFiltros = document.getElementById('formularioFiltros');
        const botonLimpiar = document.getElementById('botonLimpiar');
        const contenedorPaginacion = document.getElementById('contenedorPaginacion');

        const formularioCancion = document.getElementById('formularioCancion');
        const campoId = document.getElementById('campoId');
        const campoAccion = document.getElementById('campoAccion');
        const campoTitulo = document.getElementById('campoTitulo');
        const campoAlbum = document.getElementById('campoAlbum');
        const campoDescripcion = document.getElementById('campoDescripcion');
        const campoImagen = document.getElementById('campoImagen');
        const botonGuardar = document.getElementById('botonGuardar');
        const botonCancelarEdicion = document.getElementById('botonCancelarEdicion');

        let paginaActual = 1;

        // Obtener parametros de la URL (para mantener filtros si ya existian)
        function obtenerParametrosUrl() {
            const params = new URLSearchParams(window.location.search);
            return {
                busqueda: params.get('busqueda') || '',
                album: params.get('album') || ''
            };
        }

        function aplicarFiltrosDesdeUrl() {
            const {
                busqueda,
                album
            } = obtenerParametrosUrl();
            if (busqueda) campoBusqueda.value = busqueda;
            if (album) selectAlbum.value = album; // se terminara de setear luego de llenar el select
        }

        function renderizarTarjetas(canciones) {
            contenedorTarjetas.innerHTML = '';

            if (!canciones || canciones.length === 0) {
                contenedorTarjetas.innerHTML = '<p class="empty-state">No se encontraron canciones con esos filtros.</p>';
                return;
            }

            canciones.forEach(cancion => {
                const tarjeta = document.createElement('article');
                tarjeta.className = 'billie-eilish-card';

                const imagen = cancion.imagen || '/img/defecto.webp';

                tarjeta.innerHTML = `
                <div class="billie-eilish-card-image-wrapper">
                    <img src="${imagen}"
                         alt="Portada del álbum ${cancion.album}"
                         class="billie-eilish-card-image"
                         onerror="this.src='/img/defecto.webp';">
                </div>
                <div class="billie-eilish-card-body">
                    <h3 class="billie-eilish-card-title">${cancion.titulo}</h3>
                    <p class="billie-eilish-card-album">${cancion.album}</p>
                    <p class="billie-eilish-card-description">${cancion.descripcion}</p>
                    <div class="card-actions">
                        <button class="btn-small btn-edit" data-id="${cancion.id}">Editar</button>
                        <button class="btn-small btn-delete" data-id="${cancion.id}">Eliminar</button>
                    </div>
                </div>
            `;

                contenedorTarjetas.appendChild(tarjeta);
            });

            // Eventos para editar/eliminar desde cada tarjeta
            document.querySelectorAll('.btn-edit').forEach(boton => {
                boton.addEventListener('click', () => {
                    const id = boton.getAttribute('data-id');
                    iniciarEdicionDesdeTarjeta(id);
                });
            });

            document.querySelectorAll('.btn-delete').forEach(boton => {
                boton.addEventListener('click', () => {
                    const id = boton.getAttribute('data-id');
                    confirmarEliminacion(id);
                });
            });
        }

        function renderizarPaginacion(paginacion) {
            contenedorPaginacion.innerHTML = '';
            if (!paginacion) return;

            const {
                pagina_actual,
                paginas_totales
            } = paginacion;

            if (paginas_totales <= 1) return;

            const btnPrev = document.createElement('button');
            btnPrev.textContent = 'Anterior';
            btnPrev.className = 'btn-secondary btn-page';
            btnPrev.disabled = pagina_actual <= 1;
            btnPrev.addEventListener('click', () => {
                if (paginaActual > 1) {
                    paginaActual--;
                    obtenerCanciones();
                }
            });

            const btnNext = document.createElement('button');
            btnNext.textContent = 'Siguiente';
            btnNext.className = 'btn-secondary btn-page';
            btnNext.disabled = pagina_actual >= paginas_totales;
            btnNext.addEventListener('click', () => {
                if (paginaActual < paginas_totales) {
                    paginaActual++;
                    obtenerCanciones();
                }
            });

            const info = document.createElement('span');
            info.className = 'page-info';
            info.textContent = `Página ${pagina_actual} de ${paginas_totales}`;

            contenedorPaginacion.appendChild(btnPrev);
            contenedorPaginacion.appendChild(info);
            contenedorPaginacion.appendChild(btnNext);
        }

        function cargarAlbumesSelect(albumes, seleccionado) {
            selectAlbum.innerHTML = '<option value="">Todos los álbumes</option>';
            albumes.forEach(nombreAlbum => {
                if (!nombreAlbum) return;
                const op = document.createElement('option');
                op.value = nombreAlbum;
                op.textContent = nombreAlbum;
                selectAlbum.appendChild(op);
            });
            if (seleccionado) {
                selectAlbum.value = seleccionado;
            }
        }

        async function obtenerCanciones() {
            textoResultados.textContent = 'Cargando canciones...';

            const params = new URLSearchParams();
            const textoBusqueda = campoBusqueda.value.trim();
            const albumSeleccionado = selectAlbum.value.trim();

            if (textoBusqueda !== '') params.append('busqueda', textoBusqueda);
            if (albumSeleccionado !== '') params.append('album', albumSeleccionado);

            params.append('pagina', paginaActual.toString());

            try {
                const respuesta = await fetch(urlApi + '?' + params.toString());
                if (!respuesta.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }

                const datos = await respuesta.json();

                if (!datos.exito) {
                    throw new Error(datos.mensaje || 'Error desconocido en la API');
                }

                cargarAlbumesSelect(datos.albumes || [], datos.filtros.album || '');
                renderizarTarjetas(datos.canciones);
                renderizarPaginacion(datos.paginacion);

                const total = datos.paginacion.total_registros;
                if (total === 0) {
                    textoResultados.textContent = 'Sin resultados.';
                } else if (total === 1) {
                    textoResultados.textContent = '1 canción encontrada.';
                } else {
                    textoResultados.textContent = total + ' canciones encontradas.';
                }

            } catch (error) {
                console.error(error);
                textoResultados.textContent = 'Ocurrió un error al cargar los datos.';
                contenedorTarjetas.innerHTML = `<p class="error-state">${error.message}</p>`;
            }
        }

        // 
        // ALTA/EDICION (POST)
        // 
        function resetearFormularioCancion() {
            campoId.value = '';
            campoAccion.value = 'crear';
            formularioCancion.reset();
            botonGuardar.textContent = 'Agregar canción';
            botonCancelarEdicion.style.display = 'none';
        }

        function iniciarEdicionDesdeTarjeta(id) {
            // Buscar datos desde la tarjeta para rellenar el formulario
            const tarjeta = [...document.querySelectorAll('.btn-edit')]
                .map(b => b.closest('.billie-eilish-card'))
                .find(card => card.querySelector('.btn-edit').getAttribute('data-id') === id);

            if (!tarjeta) return;

            const titulo = tarjeta.querySelector('.billie-eilish-card-title').textContent;
            const album = tarjeta.querySelector('.billie-eilish-card-album').textContent;
            const descripcion = tarjeta.querySelector('.billie-eilish-card-description').textContent;
            const imagen = tarjeta.querySelector('img').getAttribute('src');

            campoId.value = id;
            campoAccion.value = 'editar';
            campoTitulo.value = titulo;
            campoAlbum.value = album;
            campoDescripcion.value = descripcion;
            campoImagen.value = imagen;

            botonGuardar.textContent = 'Guardar cambios';
            botonCancelarEdicion.style.display = 'inline-block';

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        async function enviarFormularioCancion(evento) {
            evento.preventDefault();

            // Validacion HTML (required)
            if (!formularioCancion.checkValidity()) {
                formularioCancion.reportValidity();
                return;
            }

            const datos = new FormData(formularioCancion);

            try {
                const respuesta = await fetch(urlApi, {
                    method: 'POST',
                    body: datos
                });

                const data = await respuesta.json();

                if (!respuesta.ok || !data.exito) {
                    let msg = data.mensaje || 'Error al guardar la canción.';
                    if (data.errores) {
                        msg += '\n\n';
                        for (const campo in data.errores) {
                            msg += `• ${data.errores[campo]}\n`;
                        }
                    }
                    Swal.fire('Error', msg, 'error');
                    return;
                }

                Swal.fire('Listo', data.mensaje || 'Operación realizada correctamente.', 'success');
                resetearFormularioCancion();
                paginaActual = 1;
                obtenerCanciones();

            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'Ocurrió un error al comunicar con el servidor.', 'error');
            }
        }

        async function confirmarEliminacion(id) {
            const resultado = await Swal.fire({
                title: '¿Eliminar canción?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (!resultado.isConfirmed) {
                return;
            }

            const datos = new FormData();
            datos.append('accion', 'eliminar');
            datos.append('id', id);

            try {
                const respuesta = await fetch(urlApi, {
                    method: 'POST',
                    body: datos
                });

                const data = await respuesta.json();

                if (!respuesta.ok || !data.exito) {
                    Swal.fire('Error', data.mensaje || 'No se pudo eliminar la canción.', 'error');
                    return;
                }

                Swal.fire('Eliminada', data.mensaje || 'La canción fue eliminada.', 'success');
                obtenerCanciones();

            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'Ocurrió un error al comunicar con el servidor.', 'error');
            }
        }

        // 
        // EVENTOS
        // 
        formularioFiltros.addEventListener('submit', function(e) {
            e.preventDefault();
            paginaActual = 1;
            obtenerCanciones();
        });

        botonLimpiar.addEventListener('click', function() {
            campoBusqueda.value = '';
            selectAlbum.value = '';
            paginaActual = 1;
            obtenerCanciones();
        });

        formularioCancion.addEventListener('submit', enviarFormularioCancion);

        botonCancelarEdicion.addEventListener('click', function() {
            resetearFormularioCancion();
        });

        document.addEventListener('DOMContentLoaded', function() {
            aplicarFiltrosDesdeUrl();
            obtenerCanciones();
        });
    </script>
</body>

</html>