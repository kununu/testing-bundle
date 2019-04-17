@Library("kununu") _

withEnv([
    "SERVICE_NAME=testing-bundle",
    "LOG_JUNIT_EXPORT=tests/.results/tests-junit.xml",
    "LOG_CLOVER_EXPORT=tests/.results/tests-clover.xml"
    ]) {
    ansiColor {
        timestamps {
            defaultPipeline.getSource()
            defaultPipeline.runPhpLibTests("--exclude-group integration")
            defaultPipeline.runSonar("php")

            if (env.BRANCH_NAME in ["master"]) {
                defaultPipeline.publishPhpPackage()
            }
        }
    }
}