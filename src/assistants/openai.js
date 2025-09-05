import OpenAI from "openai";

const openai = new OpenAI({
  apiKey: import.meta.env.VITE_OPEN_AI_API_KEY,
  dangerouslyAllowBrowser: true,
});

export class Assistant {
    #model;

    constructor(model = "gpt-4.1-mini") {
        this.#model = model;
    }

    async chat(content, history) {
        const result = await openai.chat.completions.create({
            model: this.#model,
            messages: [...history, { role: "user", content }],
        });
        return result.choices[0].message.content;
    }
}