

<template>

<div>
    <div v-for="survey in survey_responses">
        <h1>{{survey.name}}</h1>
        <li v-for="question in survey.questions">
            {{question.content}}
            <p v-for="response in question.responses">
                {{response}}
            </p>
        </li>
    </div>
</div>

</template>

<script>
export default {
    name: "response",
    props: ["survey_responses"]
};
</script>
