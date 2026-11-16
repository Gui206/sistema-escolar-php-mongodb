<footer class="rodape container-fluid px-3 py-3 text-center">
   SISTEMA ESCOLAR USANDO MONGO-DB + PHP GUILHERME BELO 
</footer>

<!-- janela para ver a foto do aluno em tamanho grande -->
<div class="modal fade" id="modalFoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title fs-6" id="modalFotoTitulo">Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body text-center p-2">
                <img id="modalFotoImg" src="" alt="" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $raiz ?? '' ?>assets/js/app.js"></script>
</body>
</html>
