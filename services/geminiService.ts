
import { GoogleGenAI, GenerateContentResponse } from "@google/genai";
import { SYSTEM_INSTRUCTION, IMAGE_GEN_INSTRUCTION, TONE_DESCRIPTIONS } from "../constants";
import { VisualGenerationType, AspectRatio, TokenUsage, TextTone } from "../types";

// Initialization must use process.env.API_KEY directly as per @google/genai guidelines.
const ai = new GoogleGenAI({ apiKey: process.env.API_KEY });

const fileToPart = (file: File): Promise<{ inlineData: { data: string; mimeType: string } }> => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onloadend = () => {
      const base64String = (reader.result as string).split(',')[1];
      resolve({
        inlineData: {
          data: base64String,
          mimeType: file.type,
        },
      });
    };
    reader.onerror = reject;
    reader.readAsDataURL(file);
  });
};

const base64ToPart = (base64String: string): { inlineData: { data: string; mimeType: string } } => {
    const parts = base64String.split(',');
    const mimeMatch = parts[0].match(/:(.*?);/);
    const mimeType = mimeMatch ? mimeMatch[1] : 'image/png';
    const data = parts[1];
    
    return {
        inlineData: {
            data,
            mimeType
        }
    };
};

interface ServiceResponse {
    content: string;
    usage?: TokenUsage;
}

export const generatePostContent = async (
  promptText: string,
  imageFile: File | null,
  tone: TextTone = 'friendly'
): Promise<ServiceResponse> => {
  try {
    const parts: any[] = [];
    if (imageFile) {
      const imagePart = await fileToPart(imageFile);
      parts.push(imagePart);
    }
    const toneInstruction = TONE_DESCRIPTIONS[tone] || TONE_DESCRIPTIONS.friendly;
    const textPrompt = `Kullanıcının isteği: ${promptText || "Can Kuruyemiş için genel bir paylaşım."}\n\nYazım Stili (TONE): ${toneInstruction}`;
    parts.push({ text: textPrompt });

    const response = await ai.models.generateContent({
      model: 'gemini-3-flash-preview',
      contents: { parts: parts },
      config: { 
          systemInstruction: SYSTEM_INSTRUCTION, 
          temperature: 0.8,
          thinkingConfig: { thinkingBudget: 0 } 
      }
    });

    return { 
        content: response.text || "İçerik üretilemedi.",
        usage: response.usageMetadata ? {
            promptTokens: response.usageMetadata.promptTokenCount || 0,
            responseTokens: response.usageMetadata.candidatesTokenCount || 0,
            totalTokens: response.usageMetadata.totalTokenCount || 0,
        } : undefined
    };
  } catch (error) {
    console.error("Gemini Error:", error);
    throw new Error("Metin üretilemedi.");
  }
};

export const generateVisualContent = async (
  promptText: string,
  imageFile: File | null,
  visualType: VisualGenerationType,
  logoBase64: string | null,
  aspectRatio: AspectRatio
): Promise<ServiceResponse> => {
  try {
    const parts: any[] = [];
    let finalPrompt = "";
    if (imageFile) parts.push(await fileToPart(imageFile));
    if (logoBase64 && visualType === VisualGenerationType.ADVERTISEMENT) parts.push(base64ToPart(logoBase64));

    if (visualType === VisualGenerationType.ENHANCE) {
        finalPrompt = `Professional enhancement of this food photo. Focus on nuts texture and warmth. ${promptText}. ${IMAGE_GEN_INSTRUCTION}`;
    } else {
        finalPrompt = `Stunning professional advertising photography for a nut shop: ${promptText || "Mixed nuts arrangement"}. ${IMAGE_GEN_INSTRUCTION}`;
    }

    parts.push({ text: finalPrompt });
    const response = await ai.models.generateContent({
      model: 'gemini-2.5-flash-image', 
      contents: { parts: parts },
      config: { imageConfig: { aspectRatio: aspectRatio } }
    });

    if (response.candidates?.[0]?.content?.parts) {
      for (const part of response.candidates[0].content.parts) {
        if (part.inlineData) {
          return {
              content: `data:image/png;base64,${part.inlineData.data}`,
              usage: response.usageMetadata ? {
                  promptTokens: response.usageMetadata.promptTokenCount || 0,
                  responseTokens: response.usageMetadata.candidatesTokenCount || 0,
                  totalTokens: response.usageMetadata.totalTokenCount || 0,
              } : undefined
          };
        }
      }
    }
    throw new Error("Görsel oluşturulamadı.");
  } catch (error: any) {
    throw new Error(error.message || "Görsel üretim hatası.");
  }
};

export const streamChat = async (
  history: { role: 'user' | 'model'; parts: { text: string }[] }[],
  onChunk: (text: string) => void
) => {
  // Added await here to resolve the promise returned by generateContentStream before iteration.
  const chat = await ai.models.generateContentStream({
    model: 'gemini-3-flash-preview',
    contents: history.map(h => ({ role: h.role === 'model' ? 'model' : 'user', parts: h.parts })),
    config: {
      systemInstruction: SYSTEM_INSTRUCTION + "\n\nKullanıcıyla samimi bir esnaf gibi konuş.",
    }
  });

  for await (const chunk of chat) {
    // chunk.text is a property, not a method.
    const chunkText = chunk.text;
    if (chunkText) onChunk(chunkText);
  }
};
