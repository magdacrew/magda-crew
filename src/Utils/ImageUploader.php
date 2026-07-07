<?php

class ImageUploader {
    
    /**
     * Valida, comprime e salva a imagem.
     * @param array $file O array do arquivo (ex: $_FILES['imagens'])
     * @param string $pastaDestino Caminho físico absoluto no servidor
     * @param int $maxSize Tamanho máximo em bytes (padrão 3MB)
     * @return string Nome do arquivo final gerado
     * @throws Exception Em caso de falha de segurança ou processamento
     */
    public static function uploadEComprimir(array $file, string $pastaDestino, int $maxSize = 3145728): string {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Erro no upload do arquivo.');
        }

        if ($file['size'] > $maxSize) {
            throw new Exception('Imagem muito grande. O limite é 3MB.');
        }

        // Validação de MIME Type real (ignora a extensão do nome do arquivo)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        $tiposPermitidos = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        if (!array_key_exists($mimeType, $tiposPermitidos)) {
            throw new Exception('Formato inválido. Apenas JPG, PNG e WEBP são permitidos.');
        }

        $extensao = $tiposPermitidos[$mimeType];
        $nomeSeguro = self::sanitizarNomeArquivo($file['name'], $extensao);
        $caminhoFisico = rtrim($pastaDestino, '/') . '/' . $nomeSeguro;

        // Criar imagem na memória para processamento
        $img = match($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($file['tmp_name']),
            'image/png'  => imagecreatefrompng($file['tmp_name']),
            'image/webp' => imagecreatefromwebp($file['tmp_name']),
            default      => false
        };

        if (!$img) {
            throw new Exception('Arquivo corrompido ou formato não reconhecido nativamente.');
        }

        // Redimensionamento inteligente (limite de 1200px de largura)
        $w = imagesx($img);
        $h = imagesy($img);
        
        if ($w > 1200) {
            $novaH = (int)($h * 1200 / $w);
            $imgRedim = imagecreatetruecolor(1200, $novaH);
            
            // Preservar transparência para PNG e WEBP
            if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
                imagealphablending($imgRedim, false);
                imagesavealpha($imgRedim, true);
                $transparent = imagecolorallocatealpha($imgRedim, 255, 255, 255, 127);
                imagefilledrectangle($imgRedim, 0, 0, 1200, $novaH, $transparent);
            }

            imagecopyresampled($imgRedim, $img, 0, 0, 0, 0, 1200, $novaH, $w, $h);
            $img = $imgRedim; // Substitui pela imagem menor
        }

        // Salvar comprimindo
        $sucesso = match($mimeType) {
            'image/jpeg' => imagejpeg($img, $caminhoFisico, 80), // 80% qualidade
            'image/png'  => imagepng($img, $caminhoFisico, 7),   // Compressão 7/9
            'image/webp' => imagewebp($img, $caminhoFisico, 80)
        };

        imagedestroy($img);

        if (!$sucesso) {
            throw new Exception('Falha ao gravar a imagem comprimida no disco.');
        }

        return $nomeSeguro;
    }

    private static function sanitizarNomeArquivo(string $nomeOriginal, string $extensaoReal): string {
        $nomeBase = pathinfo($nomeOriginal, PATHINFO_FILENAME);
        // Remove tudo que não for letra, número, hífen ou underline
        $nomeBase = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $nomeBase);
        $nomeBase = substr($nomeBase, 0, 50); // Limita tamanho
        
        return time() . '_' . uniqid() . '_' . $nomeBase . '.' . $extensaoReal;
    }
}