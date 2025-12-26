import React from 'react';
import { SavedItem } from '../types';
import ResultCard from './ResultCard';

interface SavedListProps {
  items: SavedItem[];
  onDelete: (id: string) => void;
}

const SavedList: React.FC<SavedListProps> = ({ items, onDelete }) => {
  if (items.length === 0) {
    return (
      <div className="text-center py-20 bg-white rounded-xl border border-dashed border-brand-200 shadow-sm">
        <div className="text-4xl mb-4">📭</div>
        <h3 className="text-lg font-bold text-brand-900">Arşiviniz Boş</h3>
        <p className="text-sm text-gray-500">Henüz hiçbir içeriği kaydetmediniz. Ürettiğiniz içerikleri "Kaydet" butonuyla buraya ekleyebilirsiniz.</p>
      </div>
    );
  }

  return (
    <div className="space-y-8 animate-fade-in">
      <div className="flex justify-between items-center mb-4">
          <h3 className="font-bold text-brand-900">Arşivlenen İçerikler ({items.length})</h3>
      </div>
      
      {items.map((item) => (
        <div key={item.id} className="relative group">
          <button 
            onClick={() => {
                if(confirm("Bu içeriği arşivden silmek istediğine emin misin Şafak Esnafım?")) {
                    onDelete(item.id);
                }
            }}
            className="absolute top-4 right-4 z-20 bg-red-100 text-red-600 p-1.5 rounded-full hover:bg-red-200 transition-colors shadow-sm"
            title="Arşivden Sil"
          >
            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
          
          <div className="mb-1 text-[10px] text-gray-400 font-medium px-2">
              🗓️ {new Date(item.timestamp).toLocaleString('tr-TR')}
          </div>
          
          <ResultCard 
            result={{ type: item.type as any, content: item.content }} 
            isSavedMode={true} 
          />
        </div>
      ))}
    </div>
  );
};

export default SavedList;