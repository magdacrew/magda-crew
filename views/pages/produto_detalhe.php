<?php require_once __DIR__ . '/../components/header.php'; ?>

<div class="container-produto">
    <div class="produto-galeria">
        <div class="imagem-placeholder">
            Sem Imagem
        </div>
    </div>

    <div class="produto-info">
        <p class="categoria-badge"><?= htmlspecialchars($produto['categoria_nome']) ?></p>
        <h1><?= htmlspecialchars($produto['nome']) ?></h1>
        
        <div class="preco">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></div>
        
        <p class="descricao"><?= nl2br(htmlspecialchars($produto['descricao'])) ?></p>

        <form action="/MAGDA-CREW/public/carrinho/adicionar" method="POST" class="form-compra">
            
            <?php if (count($variantes) > 0): ?>
                <label for="variante_id">Escolha a opção (Cor / Tamanho):</label>
                <select name="variante_id" id="variante_id" required>
                    <option value="" disabled selected>Selecione...</option>
                    <?php foreach ($variantes as $var): ?>
                        <option value="<?= $var['id'] ?>">
                            <?= htmlspecialchars($var['cor_nome']) ?> - Tamanho <?= htmlspecialchars($var['tamanho_nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn-comprar">Adicionar ao Carrinho</button>
            <?php else: ?>
                <p style="color: red; font-weight: bold;">Produto Esgotado no Momento.</p>
                <button type="button" class="btn-comprar" disabled style="background: #ccc;">Indisponível</button>
            <?php endif; ?>
            
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../components/footer.php'; ?>