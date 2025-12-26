import { GoogleGenAI, GenerateContentResponse } from "@google/genai";
import { SYSTEM_INSTRUCTION, IMAGE_GEN_INSTRUCTION, TONE_DESCRIPTIONS } from "../constants";
import { VisualGenerationType, AspectRatio, TokenUsage, TextTone } from "../types";

const ai = new GoogleGenAI({ apiKey: process.env.API_KEY });

/**
 * Converts a File object to a Base64 string suitable for the Gemini API.
 */
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

/**
 * Converts a base64 string (from localStorage) to a Part.
 */
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
    const textPrompt = `
      Kullanıcının isteği: ${promptText || "Can Kuruyemiş için genel bir paylaşım."}
      
      Yazım Stili (TONE): ${toneInstruction}
      
      Lütfen bu stili metnin her yerine yansıt.
    `;

    parts.push({ text: textPrompt });

    const response = await ai.models.generateContent({
      model: 'gemini-3-flash-preview',
      contents: {
        parts: parts
      },
      config: {
        systemInstruction: SYSTEM_INSTRUCTION,
        temperature: 0.8,
      }
    });

    const usage: TokenUsage | undefined = response.usageMetadata ? {
        promptTokens: response.usageMetadata.promptTokenCount || 0,
        responseTokens: response.usageMetadata.candidatesTokenCount || 0,
        totalTokens: response.usageMetadata.totalTokenCount || 0,
    } : undefined;

    return { 
        content: response.text || "Bir hata oluştu, içerik üretilemedi.",
        usage
    };

  } catch (error) {
    console.error("Gemini API Error (Text):", error);
    throw new Error("Metin içerik üretilirken bir sorun oluştu.");
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

    if (imageFile) {
      const imagePart = await fileToPart(imageFile);
      parts.push(imagePart);
    }

    if (logoBase64 && visualType === VisualGenerationType.ADVERTISEMENT) {
        const logoPart = base64ToPart(logoBase64);
        parts.push(logoPart);
    }

    if (visualType === VisualGenerationType.ENHANCE) {
        finalPrompt = `Enhance the provided food image to look like professional cinematic food photography. ${promptText ? "Additional request: " + promptText : ""}. ${IMAGE_GEN_INSTRUCTION}`;
    } else {
        finalPrompt = `Professional commercial food photography of: ${promptText || "Delicious premium nuts and snacks"}. 
        INSTRUCTION: If the prompt specifically asks to write text/words (e.g. 'Write 50% SALE'), you MUST include that text in the image. Ensure the text is spelled correctly, legible, and integrated naturally (e.g. on a chalkboard sign, price tag, or overlay).
        ${imageFile ? "Use the first provided image as visual reference." : ""} 
        ${logoBase64 ? "Integrate the provided logo (second image) naturally into the composition (e.g. on packaging)." : ""} 
        ${IMAGE_GEN_INSTRUCTION}`;
    }

    parts.push({ text: finalPrompt });

    const response = await ai.models.generateContent({
      model: 'gemini-2.5-flash-image', 
      contents: {
        parts: parts
      },
      config: {
        imageConfig: {
          aspectRatio: aspectRatio,
        }
      }
    });

    const usage: TokenUsage | undefined = response.usageMetadata ? {
        promptTokens: response.usageMetadata.promptTokenCount || 0,
        responseTokens: response.usageMetadata.candidatesTokenCount || 0,
        totalTokens: response.usageMetadata.totalTokenCount || 0,
    } : undefined;

    if (response.candidates?.[0]?.content?.parts) {
      for (const part of response.candidates[0].content.parts) {
        if (part.inlineData) {
          return {
              content: `data:image/png;base64,${part.inlineData.data}`,
              usage
          };
        }
      }
    }
    
    const textOutput = response.text;
    if (textOutput) {
        throw new Error(`Görsel üretilemedi, model şu yanıtı verdi: ${textOutput.substring(0, 100)}...`);
    }
    
    throw new Error("Görsel oluşturulamadı, model boş yanıt döndürdü.");

  } catch (error: any) {
    console.error("Gemini API Error (Image):", error);
    throw new Error(error.message || "Görsel üretilirken bir sorun oluştu.");
  }
};

export const streamChat = async (
  history: { role: 'user' | 'model'; parts: { text: string }[] }[],
  onChunk: (text: string) => void
) => {
  const chat = ai.chats.create({
    model: 'gemini-3-flash-preview',
    config: {
      systemInstruction: SYSTEM_INSTRUCTION + "\n\n**SOHBET MODU ÖZEL TALİMATI:** Kullanıcıyla samimi, yardımsever ve proaktif bir esnaf arkadaşı gibi sohbet et. Kısa ve öz cevaplar ver.",
      history: history.slice(0, -1), // Send previous history
    }
  });

  const lastMessage = history[history.length - 1].parts[0].text;
  const result = await chat.sendMessageStream({ message: lastMessage });

  for await (const chunk of result) {
    const chunkText = (chunk as GenerateContentResponse).text;
    if (chunkText) {
      onChunk(chunkText);
    }
  }
};