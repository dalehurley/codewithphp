<script setup lang="ts">
import { ref, computed } from "vue";

interface QuizOption {
  text: string;
  correct: boolean;
  explanation?: string;
}

interface QuizQuestion {
  question: string;
  options: QuizOption[];
  explanation?: string;
}

const props = defineProps<{
  title?: string;
  questions: QuizQuestion[];
}>();

const currentQuestion = ref(0);
const selectedAnswers = ref<number[]>(
  new Array(props.questions.length).fill(-1)
);
const showResults = ref<boolean[]>(
  new Array(props.questions.length).fill(false)
);
const quizComplete = ref(false);

const currentQuestionData = computed(
  () => props.questions[currentQuestion.value]
);

const canSubmit = computed(
  () => selectedAnswers.value[currentQuestion.value] !== -1
);

const score = computed(() => {
  return props.questions.reduce((acc, question, index) => {
    const selected = selectedAnswers.value[index];
    if (selected !== -1 && question.options[selected].correct) {
      return acc + 1;
    }
    return acc;
  }, 0);
});

const selectOption = (index: number) => {
  if (!showResults.value[currentQuestion.value]) {
    selectedAnswers.value[currentQuestion.value] = index;
  }
};

const submitAnswer = () => {
  showResults.value[currentQuestion.value] = true;
};

const nextQuestion = () => {
  if (currentQuestion.value < props.questions.length - 1) {
    currentQuestion.value++;
  } else {
    quizComplete.value = true;
  }
};

const resetQuiz = () => {
  currentQuestion.value = 0;
  selectedAnswers.value = new Array(props.questions.length).fill(-1);
  showResults.value = new Array(props.questions.length).fill(false);
  quizComplete.value = false;
};

const getOptionClass = (index: number) => {
  const selected = selectedAnswers.value[currentQuestion.value] === index;
  const showingResults = showResults.value[currentQuestion.value];

  if (!showingResults) {
    return selected ? "selected" : "";
  }

  const option = currentQuestionData.value.options[index];
  if (option.correct) {
    return "correct";
  }
  if (selected && !option.correct) {
    return "incorrect";
  }
  return "";
};

const getFeedback = computed(() => {
  if (!showResults.value[currentQuestion.value]) return null;

  const selected = selectedAnswers.value[currentQuestion.value];
  const isCorrect = currentQuestionData.value.options[selected].correct;

  return {
    correct: isCorrect,
    text: isCorrect
      ? currentQuestionData.value.options[selected].explanation || "✓ Correct!"
      : currentQuestionData.value.options[selected].explanation ||
        "✗ Incorrect. Try reviewing the chapter.",
  };
});
</script>

<template>
  <div class="my-8 p-6 border border-[var(--vp-c-divider)] rounded-lg bg-[var(--vp-c-bg-soft)]">
    <h3 v-if="title" class="m-0 mb-5 text-[var(--vp-c-text-1)]">{{ title }}</h3>

    <div v-if="!quizComplete">
      <div class="flex justify-between items-center mb-4">
        <span class="text-[0.9em] text-[var(--vp-c-text-2)] font-semibold"
          >Question {{ currentQuestion + 1 }} of {{ questions.length }}</span
        >
      </div>

      <div class="text-[1.1em] font-semibold mb-4 text-[var(--vp-c-text-1)]">{{ currentQuestionData.question }}</div>

      <ul class="list-none p-0">
        <li
          v-for="(option, index) in currentQuestionData.options"
          :key="index"
          :class="[
            'my-2 py-3 px-4 border border-[var(--vp-c-divider)] rounded-md cursor-pointer transition-all duration-200 bg-[var(--vp-c-bg)]',
            getOptionClass(index) === 'selected' ? 'border-[var(--vp-c-brand)] bg-[var(--vp-c-brand-soft)]' : '',
            getOptionClass(index) === 'correct' ? 'border-[var(--vp-c-green)] bg-[var(--vp-c-green-soft)]' : '',
            getOptionClass(index) === 'incorrect' ? 'border-[var(--vp-c-red)] bg-[var(--vp-c-red-soft)]' : '',
            getOptionClass(index) === '' ? 'hover:border-[var(--vp-c-brand)] hover:bg-[var(--vp-c-bg-soft)]' : ''
          ]"
          @click="selectOption(index)"
        >
          {{ option.text }}
        </li>
      </ul>

      <div
        v-if="getFeedback"
        :class="[
          'mt-4 py-3 px-4 rounded-md text-[0.95em]',
          getFeedback.correct 
            ? 'bg-[var(--vp-c-green-soft)] text-[var(--vp-c-green-darker)] border border-[var(--vp-c-green)]' 
            : 'bg-[var(--vp-c-red-soft)] text-[var(--vp-c-red-darker)] border border-[var(--vp-c-red)]'
        ]"
      >
        {{ getFeedback.text }}
      </div>

      <div class="mt-4">
        <button
          v-if="!showResults[currentQuestion]"
          class="mt-4 py-2 px-6 bg-[var(--vp-c-brand)] text-white border-none rounded-md cursor-pointer text-base transition-[background-color] duration-200 hover:bg-[var(--vp-c-brand-dark)] disabled:bg-[var(--vp-c-divider)] disabled:cursor-not-allowed"
          :disabled="!canSubmit"
          @click="submitAnswer"
        >
          Submit Answer
        </button>

        <button v-else class="mt-4 py-2 px-6 bg-[var(--vp-c-brand)] text-white border-none rounded-md cursor-pointer text-base transition-[background-color] duration-200 hover:bg-[var(--vp-c-brand-dark)] disabled:bg-[var(--vp-c-divider)] disabled:cursor-not-allowed" @click="nextQuestion">
          {{
            currentQuestion < questions.length - 1
              ? "Next Question"
              : "View Results"
          }}
        </button>
      </div>
    </div>

    <div v-else class="text-center p-5">
      <h4 class="text-[1.5em] m-0 mb-4 text-[var(--vp-c-brand)]">Quiz Complete!</h4>
      <div class="text-[1.2em] font-semibold my-4 text-[var(--vp-c-text-1)]">
        You scored {{ score }} out of {{ questions.length }} ({{
          Math.round((score / questions.length) * 100)
        }}%)
      </div>

      <div class="text-[1.1em] my-6 p-4 bg-[var(--vp-c-bg)] rounded-lg text-[var(--vp-c-text-2)]">
        <template v-if="score === questions.length">
          🎉 Perfect score! You've mastered this chapter.
        </template>
        <template v-else-if="score >= questions.length * 0.7">
          👍 Great job! You have a solid understanding of the material.
        </template>
        <template v-else>
          📚 Consider reviewing the chapter to strengthen your understanding.
        </template>
      </div>

      <button class="mt-4 py-2 px-6 bg-[var(--vp-c-brand)] text-white border-none rounded-md cursor-pointer text-base transition-[background-color] duration-200 hover:bg-[var(--vp-c-brand-dark)]" @click="resetQuiz">Retake Quiz</button>
    </div>
  </div>
</template>

