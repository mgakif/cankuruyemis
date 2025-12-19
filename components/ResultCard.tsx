import React from 'react';
import { GeneratedContent, GenerationMode } from '../types';

interface ResultCardProps {
  result: GeneratedContent;
}

// Pricing Constants (Gemini Flash Pay-as-you-go estimates)
const PRICE_PER_1M_INPUT_USD = 0.10;
const PRICE_PER_1M_OUTPUT_USD = 0.40;
const PRICE_PER_IMAGE_USD = 0.040; // Approx standard image generation cost
const USD_TO_TRY = 36.5; // Yaklaşık kur

const ResultCard: React.FC<ResultCardProps> = ({ result }) => {
  const handleCopy = () => {
    if (result.type === GenerationMode.TEXT) {
      navigator.clipboard.writeText(result.content);
      alert("Metin panoya kopyalandı! 📋");
    } else {
      // For images, we try to trigger a download
      const link = document.createElement('a');
      link.href = result.content;
      link.download = `can-kuruyemis-gorsel-${Date.now()}.png`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  };

  // Cost Calculation
  let costUSD = 0;
  let isEstimate = false;

  if (result.type === GenerationMode.IMAGE) {
      // Görseller genellikle adet başı ücretlendirilir, token hesabı yanıltıcı olabilir.
      costUSD = PRICE_PER_IMAGE_USD;
      isEstimate = true;
  } else if (result.tokenUsage) {
      const inputCost = (result.tokenUsage.promptTokens / 1_000_000) * PRICE_PER_1M_INPUT_USD;
      const outputCost = (result.tokenUsage.responseTokens / 1_000_000) * PRICE_PER_1M_OUTPUT_USD;
      costUSD = inputCost + outputCost;
  }

  const costTRY = costUSD * USD_TO_TRY;
  
  // Format currency (show tiny amounts accurately)
  const formatMoney = (amount: number, currency: 'USD' | 'TRY') => {
      if (amount === 0) return "0";
      if (amount < 0.01) return `< 0.01 ${currency}`; // For very small amounts
      return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: currency, minimumFractionDigits: 2, maximumFractionDigits: 4 }).format(amount);
  };

  return (
    <div className="bg-white rounded-xl shadow-lg border border-brand-200 overflow-hidden animate-fade-in-up">
      <div className="bg-brand-50 px-6 py-4 border-b border-brand-100 flex justify-between items-center">
        <h2 className="font-bold text-brand-900 flex items-center gap-2">
          {result.type === GenerationMode.TEXT ? (
            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-brand-600" viewBox="0 0 20 20" fill="currentColor">
              <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clipRule="evenodd" />
            </svg>
          ) : (
             <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-brand-600" viewBox="0 0 20 20" fill="currentColor">
              <path fillRule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clipRule="evenodd" />
            </svg>
          )}
          {result.type === GenerationMode.TEXT ? "Hazırlanan Metin" : "Stüdyo Çekimi"}
        </h2>
        <button 
          onClick={handleCopy}
          className="text-xs font-semibold bg-white text-brand-700 px-3 py-1.5 rounded border border-brand-200 hover:bg-brand-100 transition"
        >
          {result.type === GenerationMode.TEXT ? "Kopyala" : "İndir"}
        </button>
      </div>
      
      <div className="p-6">
        {result.type === GenerationMode.TEXT ? (
          <div className="whitespace-pre-wrap font-medium text-gray-800 prose prose-brand max-w-none">
             {result.content.split('###').map((section, index) => {
               if (!section.trim()) return null;
               const [title, ...body] = section.split('\n');
               return (
                 <div key={index} className="mb-6 last:mb-0">
                   <h3 className="text-lg font-bold text-brand-800 mb-2">{title}</h3>
                   <p className="text-gray-700 leading-relaxed">{body.join('\n').trim()}</p>
                 </div>
               );
             })}
          </div>
        ) : (
          <div className="flex flex-col items-center">
            <div className="rounded-lg overflow-hidden shadow-xl border border-gray-200 mb-2 w-full max-w-lg">
              <img src={result.content} alt="Generated AI Food Art" className="w-full h-auto" />
            </div>
            <p className="text-sm text-gray-500 italic mt-2">
              *Yapay zeka ile "Cinematic Food Photography" stilinde üretilmiştir.
            </p>
          </div>
        )}
      </div>
      
      {/* Token & Cost Usage Footer */}
      <div className="bg-gray-50 px-6 py-3 border-t border-brand-100">
        <div className="flex flex-col sm:flex-row justify-between items-center gap-2 text-xs text-gray-500">
          <div className="flex flex-wrap items-center gap-x-4 gap-y-1">
             {result.tokenUsage && (
                <>
                   <span title="Gönderilen girdi miktarı">📥 {result.tokenUsage.promptTokens}</span>
                   <span title="Üretilen çıktı miktarı">📤 {result.tokenUsage.responseTokens}</span>
                   <span className="text-brand-600 font-bold" title="Toplam işlem tokeni">Σ {result.tokenUsage.totalTokens} Token</span>
                </>
             )}
             
             <div className="flex items-center gap-1 pl-2 ml-2 border-l border-gray-300">
                <span className="font-semibold text-gray-600">Maliyet:</span>
                <span className="text-green-600 font-bold bg-green-50 px-1.5 rounded">
                   {formatMoney(costTRY, 'TRY')}
                </span>
                <span className="text-gray-400 text-[10px]">({formatMoney(costUSD, 'USD')})</span>
                {isEstimate && <span className="text-[10px] text-gray-400" title="Görsel maliyeti tahmini olarak hesaplanmıştır">*Tahmini</span>}
             </div>
          </div>
          <div>
            Can Kuruyemiş Asistanı &bull; AI
          </div>
        </div>
      </div>
    </div>
  );
};

export default ResultCard;