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
  <div class="quiz-container">
    <h3 v-if="title" class="quiz-title">{{ title }}</h3>

    <div v-if="!quizComplete" class="quiz-content">
      <div class="quiz-header">
        <span class="quiz-counter"
          >Question {{ currentQuestion + 1 }} of {{ questions.length }}</span
        >
      </div>

      <div class="quiz-question">{{ currentQuestionData.question }}</div>

      <ul class="quiz-options">
        <li
          v-for="(option, index) in currentQuestionData.options"
          :key="index"
          :class="['quiz-option', getOptionClass(index)]"
          @click="selectOption(index)"
        >
          {{ option.text }}
        </li>
      </ul>

      <div
        v-if="getFeedback"
        :class="[
          'quiz-feedback',
          getFeedback.correct ? 'correct' : 'incorrect',
        ]"
      >
        {{ getFeedback.text }}
      </div>

      <div class="quiz-actions">
        <button
          v-if="!showResults[currentQuestion]"
          class="quiz-submit"
          :disabled="!canSubmit"
          @click="submitAnswer"
        >
          Submit Answer
        </button>

        <button v-else class="quiz-submit" @click="nextQuestion">
          {{
            currentQuestion < questions.length - 1
              ? "Next Question"
              : "View Results"
          }}
        </button>
      </div>
    </div>

    <div v-else class="quiz-results">
      <h4 class="results-title">Quiz Complete!</h4>
      <div class="results-score">
        You scored {{ score }} out of {{ questions.length }} ({{
          Math.round((score / questions.length) * 100)
        }}%)
      </div>

      <div class="results-message">
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

      <button class="quiz-submit" @click="resetQuiz">Retake Quiz</button>
    </div>
  </div>
</template>

<style scoped>
.quiz-title {
  margin: 0 0 20px;
  color: var(--vp-c-text-1);
}

.quiz-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.quiz-counter {
  font-size: 0.9em;
  color: var(--vp-c-text-2);
  font-weight: 600;
}

.quiz-actions {
  margin-top: 16px;
}

.quiz-results {
  text-align: center;
  padding: 20px;
}

.results-title {
  font-size: 1.5em;
  margin: 0 0 16px;
  color: var(--vp-c-brand);
}

.results-score {
  font-size: 1.2em;
  font-weight: 600;
  margin: 16px 0;
  color: var(--vp-c-text-1);
}

.results-message {
  font-size: 1.1em;
  margin: 24px 0;
  padding: 16px;
  background-color: var(--vp-c-bg);
  border-radius: 8px;
  color: var(--vp-c-text-2);
}
</style>
