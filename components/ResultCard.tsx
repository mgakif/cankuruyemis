import React, { useState } from 'react';
import { GeneratedContent, GenerationMode, SavedItem } from '../types';
import { uploadToGoogleDrive } from '../services/driveService';

interface ResultCardProps {
  result: GeneratedContent;
  onSave?: (item: Omit<SavedItem, 'id' | 'timestamp'>) => void;
  onDesignAd?: (text: string) => void;
  isSavedMode?: boolean;
}

const PRICE_PER_1M_INPUT_USD = 0.10;
const PRICE_PER_1M_OUTPUT_USD = 0.40;
const PRICE_PER_IMAGE_USD = 0.040;
const USD_TO_TRY = 36.5;

const ResultCard: React.FC<ResultCardProps> = ({ result, onSave, onDesignAd, isSavedMode = false }) => {
  const [isUploadingToDrive, setIsUploadingToDrive] = useState(false);

  const handleCopy = async () => {
    if (result.type === 'TEXT') {
      navigator.clipboard.writeText(result.content);
      alert("Metin panoya kopyalandı! 📋");
    } else {
      try {
        const response = await fetch(result.content);
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `can-kuruyemis-${Date.now()}.png`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
      } catch (err) {
        console.error("Download error:", err);
      }
    }
  };

  const handleDriveUpload = async () => {
    if (result.type !== 'IMAGE') return;
    setIsUploadingToDrive(true);
    await uploadToGoogleDrive(result.content, `can-kuruyemis-${Date.now()}.png`);
    setIsUploadingToDrive(false);
  };

  const handleSaveInternal = () => {
    if (onSave) {
        onSave({
            type: result.type as 'TEXT' | 'IMAGE',
            content: result.content,
            title: result.type === 'TEXT' ? "Sosyal Medya Metni" : "Stüdyo Çekimi"
        });
    }
  };

  let costUSD = result.type === 'IMAGE' ? PRICE_PER_IMAGE_USD : 0;
  if (result.tokenUsage) {
      costUSD = ((result.tokenUsage.promptTokens / 1_000_000) * PRICE_PER_1M_INPUT_USD) + 
                ((result.tokenUsage.responseTokens / 1_000_000) * PRICE_PER_1M_OUTPUT_USD);
  }
  const costTRY = costUSD * USD_TO_TRY;
  
  const formatMoney = (amount: number, currency: 'USD' | 'TRY') => {
      if (amount === 0) return currency === 'TRY' ? "0,00 ₺" : "$0.00";
      return new Intl.NumberFormat('tr-TR', { style: 'currency', currency, minimumFractionDigits: 2, maximumFractionDigits: 4 }).format(amount);
  };

  return (
    <div className="bg-white rounded-xl shadow-lg border border-brand-200 overflow-hidden animate-fade-in-up mb-6">
      <div className="bg-brand-50 px-4 sm:px-6 py-4 border-b border-brand-100 flex flex-wrap justify-between items-center gap-2">
        <h2 className="font-bold text-brand-900 flex items-center gap-2">
          {result.type === 'TEXT' ? "📋 Hazırlanan Metin" : "🖼️ Stüdyo Çekimi"}
        </h2>
        <div className="flex gap-2">
            {!isSavedMode && (
                <button onClick={handleSaveInternal} className="text-xs font-bold bg-green-600 text-white px-3 py-1.5 rounded hover:bg-green-700 shadow-sm transition">
                    Kaydet
                </button>
            )}
            {result.type === 'IMAGE' && (
                <button 
                  disabled={isUploadingToDrive}
                  onClick={handleDriveUpload} 
                  className={`text-xs font-bold px-3 py-1.5 rounded shadow-sm transition ${isUploadingToDrive ? 'bg-gray-300' : 'bg-blue-600 text-white hover:bg-blue-700'}`}
                >
                    {isUploadingToDrive ? "..." : "Drive'a At"}
                </button>
            )}
            <button onClick={handleCopy} className="text-xs font-bold bg-white text-brand-700 px-3 py-1.5 rounded border border-brand-200 hover:bg-brand-100 shadow-sm transition">
                {result.type === 'TEXT' ? "Kopyala" : "İndir"}
            </button>
        </div>
      </div>
      
      <div className="p-4 sm:p-6">
        {result.type === 'TEXT' ? (
          <div className="space-y-4">
            <div className="whitespace-pre-wrap font-medium text-gray-800 prose prose-brand max-w-none">
               {result.content.split('###').map((section, index) => {
                 if (!section.trim()) return null;
                 const lines = section.trim().split('\n');
                 const title = lines[0];
                 const body = lines.slice(1).join('\n');
                 return (
                   <div key={index} className="mb-4 last:mb-0 bg-gray-50/50 p-3 rounded-lg border border-gray-100">
                     <h3 className="text-sm font-bold text-brand-800 mb-1 border-b border-brand-100 pb-1">{title}</h3>
                     <div className="text-sm text-gray-700 leading-relaxed pt-1">{body}</div>
                   </div>
                 );
               })}
            </div>
            
            {!isSavedMode && onDesignAd && (
              <button 
                onClick={() => onDesignAd(result.content)}
                className="w-full mt-4 bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-xl flex items-center justify-center gap-2 shadow-md transition-all active:scale-95"
              >
                <span>✨ Bu Analizle Reklam Görseli Tasarla</span>
              </button>
            )}
          </div>
        ) : (
          <div className="flex flex-col items-center">
            <div className="rounded-lg overflow-hidden shadow-md border border-gray-200 w-full max-w-lg bg-gray-100 min-h-[200px] flex items-center justify-center">
              <img src={result.content} alt="Can Kuruyemiş Reklamı" className="w-full h-auto" />
            </div>
          </div>
        )}
      </div>
      
      {!isSavedMode && (
        <div className="bg-gray-50 px-6 py-2 border-t border-brand-100 flex justify-between items-center text-[10px] text-gray-400">
            <span>Maliyet Tahmini</span>
            <span className="font-bold text-green-600">{formatMoney(costTRY, 'TRY')}</span>
        </div>
      )}
    </div>
  );
};

export default ResultCard;