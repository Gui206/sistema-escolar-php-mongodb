// Scripts do Sistema Escolar
document.addEventListener('DOMContentLoaded', () => {

    // ---------- máscaras simples ----------
    const mascaras = {
        cpf: v => v.replace(/\D/g, '').slice(0, 11)
            .replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2'),
        cep: v => v.replace(/\D/g, '').slice(0, 8).replace(/(\d{5})(\d)/, '$1-$2'),
        telefone: v => {
            const n = v.replace(/\D/g, '').slice(0, 11);
            return n.length > 10
                ? n.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3')
                : n.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3').replace(/-$/, '');
        },
    };
    document.querySelectorAll('[data-mascara]').forEach(campo => {
        const aplicar = () => { campo.value = mascaras[campo.dataset.mascara](campo.value); };
        campo.addEventListener('input', aplicar);
        aplicar();
    });

    // ---------- preenche o endereço pelo CEP (AJAX com a API do ViaCEP) ----------
    const cep = document.getElementById('cep');
    if (cep) {
        const aviso = document.getElementById('cepAviso');
        cep.addEventListener('blur', async () => {
            const numeros = cep.value.replace(/\D/g, '');
            if (numeros.length !== 8) return;
            aviso.textContent = 'Buscando endereço...';
            try {
                const resposta = await fetch(`https://viacep.com.br/ws/${numeros}/json/`);
                const dados = await resposta.json();
                if (dados.erro) { aviso.textContent = 'CEP não encontrado.'; return; }
                document.getElementById('logradouro').value = dados.logradouro || '';
                document.getElementById('bairro').value = dados.bairro || '';
                document.getElementById('cidade').value = dados.localidade || '';
                document.getElementById('estado').value = dados.uf || '';
                aviso.textContent = 'Endereço preenchido pelo CEP.';
                document.getElementById('numero').focus();
            } catch (e) {
                aviso.textContent = 'Não foi possível consultar o CEP. Preencha manualmente.';
            }
        });
    }

    // ---------- prévia da foto escolhida ----------
    const inputFoto = document.getElementById('foto');
    if (inputFoto) {
        inputFoto.addEventListener('change', () => {
            const arquivo = inputFoto.files[0];
            if (!arquivo) return;
            const previa = document.getElementById('previaFoto');
            previa.innerHTML = '';
            const img = document.createElement('img');
            img.src = URL.createObjectURL(arquivo);
            img.alt = 'Prévia da foto';
            previa.appendChild(img);
            const remover = document.getElementById('removerFoto');
            if (remover) remover.checked = false;
        });
    }

    // ---------- abre a foto grande ao clicar ----------
    const modal = document.getElementById('modalFoto');
    if (modal) {
        modal.addEventListener('show.bs.modal', evento => {
            const link = evento.relatedTarget;
            document.getElementById('modalFotoImg').src = link.dataset.foto;
            document.getElementById('modalFotoImg').alt = 'Foto de ' + link.dataset.nome;
            document.getElementById('modalFotoTitulo').textContent = link.dataset.nome;
        });
    }

    // ---------- confirmação antes de excluir ----------
    document.querySelectorAll('form[data-confirmar]').forEach(form => {
        form.addEventListener('submit', e => {
            if (!confirm(form.dataset.confirmar)) e.preventDefault();
        });
    });
});
